<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\CartModel;
use App\Models\ProductModel;
use Exception;

class CartController extends BaseController
{
    // -----------------------------------------------------------------------------------
    // 함수명   : myCart()
    // 설명     : 사용자의 장바구니를 조회하는 함수
    //            로그인한 경우 DB에서, 비로그인한 경우 세션에서 장바구니 데이터를 가져옴
    //            소프트 삭제된 제품이 장바구니에 포함된 경우 해당 항목을 필터링
    //
    // param    : 없음
    //
    // return   : View - 장바구니 페이지 반환
    // -----------------------------------------------------------------------------------
    public function myCart() {
        if (Auth::check()) {
            // 로그인한 사용자
            $cartItems = CartModel::with(['product' => function($query) {
                    $query->withTrashed(); // 소프트 딜리트된 제품 포함
                }])
                ->where('user_id', Auth::id())
                ->get()
                ->filter(function($item) {
                    // 제품이 없거나 소프트 딜리트된 경우 장바구니 항목을 제거
                    return $item->product !== null && !$item->product->trashed();
                });
            return view('myCart', compact('cartItems')); // 장바구니 페이지 반환
        } else {
            // 비로그인 사용자
            $cart = session()->get('cart', []); // 세션에서 장바구니 데이터 불러오기
            // 세션 데이터가 비어 있는지 확인
            if (empty($cart)) {
                // 장바구니가 비어 있을 때 처리
                return view('myCart', ['cartItems' => []]);
            }

            $proIds     = array_keys($cart); // 세션에 저장된 상품 ID 배열 추출
            $products   = ProductModel::whereIn('pro_id', $proIds)->get();
            $cartItems  = [];
            foreach ($products as $product) {
                $proId = $product->pro_id;
                if (isset($cart[$proId])) {
                    // 세션에서 수량 및 cartProId 불러오기
                    $quantity = $cart[$proId]['quantity'];

                    // 상품 정보와 수량을 결합
                    $cartItems[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                    ];
                }
            }
            return view('myCart', compact('cartItems')); // 게스트 장바구니 페이지 반환
        }
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : addCart()
    // 설명     : 사용자가 선택한 상품을 장바구니에 추가하는 함수
    //            로그인한 경우 DB에, 비로그인한 경우 세션에 장바구니 데이터를 저장
    //
    // param    : Request $req - 클라이언트에서 전달한 상품 ID와 수량 요청 객체
    //              - proId    : 장바구니에 추가할 상품의 고유 ID
    //              - quantity : 상품 수량
    //
    // return   : JsonResponse - 장바구니 추가 성공 또는 실패 메시지를 JSON 형식으로 반환
    // -----------------------------------------------------------------------------------
    public function addCart(Request $req) {
        $req->validate([
            'proId'    => 'required|exists:product,pro_id',
            'quantity'  => 'required|integer|min:1'
        ]);
    
        $proId = $req->proId;
        $quantity = $req->quantity;
    
        // 로그인 여부 확인
        if (Auth::check()) {
            // 로그인한 경우, DB에 장바구니 저장
            $userId = Auth::id();
    
            DB::beginTransaction(); // 트랜잭션 시작
    
            try {
                // 기존 수량 가져오기
                $exCartItem = CartModel::where('user_id', $userId)->where('pro_id', $proId)->first();

                // 수량 계산
                $newQuantity = $exCartItem ? $exCartItem->quantity + $quantity : $quantity;

                // 장바구니에 추가
                $cartItem = CartModel::updateOrCreate(
                    ['user_id'  => $userId, 'pro_id' => $proId], // 조건
                    ['quantity' => $newQuantity] // 수량
                );

                DB::commit(); // 트랜잭션 커밋
            } catch (Exception $e) {
                DB::rollBack(); // 오류 발생 시 트랜잭션 롤백
                return response()->json(['msg' => '장바구니 추가 중 오류가 발생했습니다.'], 500);
            }
        } else {
            // 로그인하지 않은 경우, 세션에 장바구니 저장
            $cart = session()->get('cart', []);
    
            // 상품이 이미 장바구니에 있는지 확인
            if (isset($cart[$proId])) {
                $cart[$proId]['quantity'] += $quantity; // 수량 증가
            } else {
                $cart[$proId] = [ // 새로운 상품 추가
                    'quantity' => $quantity, // 수량 설정
                ];
            }
    
            session()->put('cart', $cart);
        }
        return response()->json(['msg' => '장바구니에 상품이 추가되었습니다.<br>장바구니로 이동하시겠습니까?'], 200);
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : uptCart()
    // 설명     : 사용자의 장바구니에 있는 상품의 수량을 업데이트하는 함수
    //            로그인한 경우 DB에, 비로그인한 경우 세션에 저장된 수량을 수정
    //
    // param    : Request $req - 클라이언트에서 전달한 상품 ID와 수량 요청 객체
    //              - proId    : 수정할 상품의 고유 ID
    //              - quantity : 수정할 수량
    //
    // return   : JsonResponse - 장바구니 업데이트 성공 또는 실패 메시지를 JSON 형식으로 반환
    // -----------------------------------------------------------------------------------
    public function uptCart(Request $req) {
        $req->validate([
            'proId'    => 'required|exists:product,pro_id',
            'quantity'  => 'required|integer|min:1'
        ]);

        $proId = $req->proId;
        $quantity = $req->quantity;

        // 로그인 여부 확인
        if (Auth::check()) {
            // 로그인한 경우, DB에 장바구니 저장
            $userId = Auth::id();
    
            DB::beginTransaction(); // 트랜잭션 시작
            
            try {
                $exCartItem = CartModel::where('user_id', $userId)->where('pro_id', $proId)->first();
                $exCartItem->quantity = $quantity;
                $exCartItem->save();

                DB::commit(); // 트랜잭션 커밋
                return response()->json(['msg' => '성공적으로 수정되었습니다.', 'quantity' => $quantity], 200);
            } catch (Exception $e) {
                DB::rollBack(); // 오류 발생 시 트랜잭션 롤백
                return response()->json(['msg' => '장바구니 수정 중 오류가 발생했습니다.'], 500);
            }
        } else {
            // 로그인하지 않은 경우, 세션에 장바구니 저장
            $cart = session()->get('cart', []);
    
            // 상품이 이미 장바구니에 있는지 확인
            if (isset($cart[$proId])) {
                $cart[$proId]['quantity'] = $quantity;
            }
    
            session()->put('cart', $cart);
    
            return response()->json(['msg' => '성공적으로 수정되었습니다.', 'quantity' => $quantity], 200);
        }
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : delCart()
    // 설명     : 장바구니에서 선택한 상품을 삭제하는 함수
    //            로그인한 경우 DB에서, 비로그인한 경우 세션에서 상품을 삭제
    //
    // param    : Request $req - 클라이언트에서 전달한 상품 ID 배열 요청 객체
    //              - proIds   : 삭제할 상품들의 고유 ID 배열
    //
    // return   : JsonResponse - 장바구니 삭제 성공 또는 실패 메시지를 JSON 형식으로 반환
    // -----------------------------------------------------------------------------------
    public function delCart(Request $req) {
        Log::debug($req);
        $req->validate([
            'proIds'    => 'required|array',
            'proIds.*'  => 'integer|exists:product,pro_id'
        ]);
    
        $proIds = $req->proIds;
    
        // 로그인 여부 확인
        if (Auth::check()) {
            // 로그인한 경우, DB에 저장된 장바구니 삭제
            $userId = Auth::id();
            DB::beginTransaction(); // 트랜잭션 시작
    
            try {
                // 카트 가져오기
                $cartItems = CartModel::where('user_id', $userId)->whereIn('pro_id', $proIds)->get();

                if ($cartItems->isEmpty()) {
                    return response()->json(['msg' => '상품을 찾을 수 없습니다.', 'success' => false]);
                }
                // 상품들을 소프트 딜리트
                foreach ($cartItems as $cartItem) {
                    $cartItem->delete();
                }

                DB::commit(); // 트랜잭션 커밋
            } catch (Exception $e) {
                DB::rollBack(); // 오류 발생 시 트랜잭션 롤백
                return response()->json(['msg' => '삭제 중 오류가 발생했습니다.'], 500);
            }
        } else {
            // 로그인하지 않은 경우, 세션의 장바구니 삭제
            $cart = session()->get('cart', []);

            // 세션에서 상품 삭제
            foreach ($proIds as $proId) {
                if (isset($cart[$proId])) {
                    unset($cart[$proId]); // 해당 상품 삭제
                }
            }

            session()->put('cart', $cart);
        }
        return response()->json(['msg' => '상품이 삭제되었습니다.', 'success' => true]);
    }
}
