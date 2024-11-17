<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CartModel;
use App\Models\OrderModel;
use App\Models\AddressModel;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class OrderController extends Controller
{
    // -----------------------------------------------------------------------------------
    // 함수명   : checkout()
    // 설명     : 장바구니에서 선택된 상품을 확인하고, 결제 페이지에 필요한 데이터를 준비하는 함수
    //            상품의 상세 정보와 배송지 정보를 조회하여 뷰로 전달
    //
    // param    : Request $req - 클라이언트에서 전달한 요청 객체
    //              - items : 선택된 상품의 ID 및 수량이 포함된 배열
    //
    // return   : View - 결제 페이지 (checkout) 뷰 반환, 필요한 데이터 포함
    //              - items           : 선택된 상품의 상세 정보 배열
    //              - addresses       : 사용자의 배송지 정보 (로그인 시에만)
    //              - defaultAddress  : 사용자의 기본 배송지 (로그인 시에만)
    //              - totalAmount     : 총 결제 금액
    //              - productNames    : 상품명 목록 (복수일 경우 '외 n개' 형식)
    // -----------------------------------------------------------------------------------
    public function checkout(Request $req) {
        // 선택된 상품 목록을 받아옴
        $selectedItems = $req->items;

        // JSON 형태로 받아온 데이터를 PHP 배열로 변환
        $items = array_map(function ($item) {
            return json_decode($item, true);
        }, $selectedItems);
        
        // 상품 ID 배열 생성
        $itemIds = array_column($items, 'id');
        
        // 상품 ID(pro_id)로 데이터베이스에서 상품 정보 조회
        $products = ProductModel::whereIn('pro_id', $itemIds)->get()->keyBy('pro_id');

        // 각 선택된 아이템에 수량 정보 추가 및 상세 정보 설정
        $itemsWithDetails = array_map(function ($item) use ($products) {
            if (isset($products[$item['id']])) {
                $product = $products[$item['id']];
                return [
                    'id' => $product->pro_id,
                    'name' => $product->name,
                    'img' => $product->img,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'totalPrice' => $product->price * $item['quantity'],
                ];
            }
            return null;
        }, $items);

        // null 값 제거
        $itemsWithDetails = array_filter($itemsWithDetails);

        $addresses = null;
        $defaultAddress = null;

        // 로그인 상태라면 배송지 정보 전달
        if (Auth::check()) {
            // 사용자의 모든 배송지 정보 가져오기
            $addresses = AddressModel::where('user_id', Auth::user()->user_id)->where('default', true)->get();

            // 기본 배송지 정보만 별도로 가져오기
            $defaultAddress = $addresses->firstWhere('default', true);
        }

        // 총 결제 금액 계산
        $totalAmount = array_sum(array_column($itemsWithDetails, 'totalPrice'));

        // 상품명 목록 형식
        $productNamesArray = array_column($itemsWithDetails, 'name');
        if (count($productNamesArray) === 1) {
            $productNames = $productNamesArray[0]; // 하나일 경우 단일 이름만 사용
        } else {
            $productNames = $productNamesArray[0] . ' 외 ' . (count($productNamesArray) - 1) . '개';
        }

        return view('checkout')->with([
            'items' => $itemsWithDetails,
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress,
            'totalAmount' => $totalAmount,
            'productNames' => $productNames,
        ]);
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : lookup()
    // 설명     : 비회원 주문조회 페이지
    // 
    // param    : 없음
    // 
    // return   : View - 비회원 주문조회 페이지 (guestLookup) 뷰 반환
    // -----------------------------------------------------------------------------------
    public function lookup() {
        return view('guestLookup');
    }   

    // -----------------------------------------------------------------------------------
    // 함수명   : lookupResult()
    // 설명     : 주문번호로 주문 정보 조회
    // param    : Request $req - 클라이언트에서 전달한 요청 객체
    //              - guest_uuid : 주문번호
    // 
    // return   : JSON - 주문 정보
    // -----------------------------------------------------------------------------------
    public function lookupResult(Request $req) {
        $guest_uuid = $req->guest_uuid;

        $order = OrderModel::with(['orderItems.product', 'payment', 'address'])
            ->where('guest_uuid', $guest_uuid)
            ->first();

        if (!$order) {
            return response()->json([
                    'success' => false,
                    'message' => '주문 정보를 찾을 수 없습니다.',
                    'order'   => null,
                ]);
        }

        return response()->json([
                'success' => true,
                'message' => '주문 정보를 조회했습니다.',
                'order' => $order,
            ]);
    }
}
