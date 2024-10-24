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

class CartController extends BaseController
{
    // 장바구니
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

    // 장바구니 넣기
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

    // 장바구니 업데이트
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

    // 장바구니에서 상품 삭제
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
