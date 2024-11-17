<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CartModel;
use App\Models\OrderModel;
use Illuminate\Support\Str;
use App\Models\AddressModel;
use App\Models\PaymentModel;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use App\Models\OrderItemsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class PaymentController extends Controller
{
    // -----------------------------------------------------------------------------------
    // 함수명   : storeOrders()
    // 설명     : 결제 완료 후 주문 데이터를 저장하고 장바구니를 비우는 함수
    //            로그인 여부에 따라 주문 정보를 처리하고, 비회원의 경우 UUID를 생성하여 저장
    //
    // param    : Request $req - 클라이언트에서 전달한 요청 객체
    //              - merchant_uid       : 결제 고유 ID
    //              - amount             : 총 결제 금액
    //              - products           : 주문한 상품 목록 (ID, 이름, 가격, 수량 포함)
    //
    // return   : RedirectResponse - 결제 성공 시 메인 페이지로 리다이렉트
    //              - 성공 시: '결제가 완료되었습니다.' 알림 포함
    //              - 실패 시: '결제 처리 중 오류가 발생했습니다.' 알림 포함
    // -----------------------------------------------------------------------------------
    public function storeOrders(Request $req) {
        Log::debug($req->all());
        // 데이터 유효성 검사
        $validatedData = $req->validate([
            'merchant_uid'          => 'required|string',
            'amount'                => 'required|numeric',
            'products'              => 'required|array',
            'products.*.product_id' => 'required|integer|exists:product,pro_id',
            'products.*.name'       => 'required|string',
            'products.*.price'      => 'required|numeric',
            'products.*.quantity'   => 'required|integer',
        ]);

        // 트랜잭션 시작
        DB::beginTransaction();
        try {
            // 로그인 여부에 따라 user_id 설정 (비회원일 경우 NULL)
            $isLogin = auth()->check();
            $userIdentifier = $isLogin ? auth()->id() : null;
            $guestUuid      = $isLogin ? null : Str::uuid(); // 비회원일 경우 UUID 생성

            if ($req->addPostcode) {
                $data = [
                    'postcode'      => $req->addPostcode,
                    'address'       => $req->addAddress,
                    'detailAddress' => $req->addDetailAddress,
                    'extraAddress'  => $req->addExtraAddress,
                    'recipient'     => $req->addRecipient,
                    'phone'         => $req->addPhone,
                ];

                if (!$isLogin) {
                    $data['guest_uuid'] = $guestUuid;
                }

                $address = AddressModel::create($data);
            }

            // 주문 테이블에 데이터 저장
            $order = OrderModel::create([
                'user_id'       => $userIdentifier, // 로그인된 유저 PK 또는 비회원일 경우 NULL
                'guest_uuid'    => $guestUuid,    // 비회원일 경우 UUID, 회원일 경우 NULL
                'add_id'        => $req->address_id ?: $address->add_id,
            ]);

            // payment 테이블에 결제 정보 저장
            $payment = PaymentModel::create([
                'ord_id'        => $order->ord_id,
                'merchant_uid'  => $validatedData['merchant_uid'],
                'price'         => $validatedData['amount'],
                'status'        => 'P'
            ]);

            // order_items 테이블에 각 상품 정보 저장
            foreach ($validatedData['products'] as $product) {
                OrderItemsModel::create([
                    'ord_id'    => $order->ord_id,
                    'pro_id'    => $product['product_id'],
                    'quantity'  => $product['quantity'],
                    'price'     => $product['price'],
                ]);
            }

            // 회원인 경우 장바구니 테이블에서 해당 상품 삭제
            if (auth()->check()) {
                foreach ($validatedData['products'] as $product) {
                    CartModel::where('user_id', auth()->id())
                        ->where('pro_id', $product['product_id'])
                        ->delete();
                }
            } else {
                // 비회원인 경우 세션에서 해당 상품 삭제
                $cart = session()->get('cart', []);
                foreach ($validatedData['products'] as $product) {
                    $productId = $product['product_id'];
                    if (isset($cart[$productId])) {
                        unset($cart[$productId]);
                    }
                }
                session()->put('cart', $cart); // 변경된 카트를 세션에 다시 저장
            }

            DB::commit();

            if ($isLogin) {
                return redirect()->route('users.mypage')->with('alert', '주문이 완료되었습니다.');
            }
            return redirect()->route('orders.lookup')->with('alert', '주문이 완료되었습니다.<br>주문번호 : ' . $guestUuid);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug($e);

            // cancelOrder 호출
            $this->cancelOrder(new Request([
                'merchant_uid' => $validatedData['merchant_uid'],
                'price'        => $validatedData['amount'],
            ]));
            return redirect()->route('main')->with('alert', '결제 처리 중 오류가 발생했습니다.');
        }
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : cancelOrder()
    //
    // param    : Request $req - 클라이언트에서 전달한 요청 객체    
    //              - merchant_uid : 결제 고유 ID
    //              - price        : 총 결제 금액
    //
    // return   : 
    //
    // 설명     : 주문 취소 함수
    // -----------------------------------------------------------------------------------
    public function cancelOrder(Request $req) {
        try {
            DB::beginTransaction();
            $accessToken = $this->getToken();

            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => $accessToken
            ])->post("https://api.iamport.kr/payments/cancel", [
                'merchant_uid' => $req->merchant_uid, // 주문번호
                'amount'       => $req->price, // 환불할 금액
                'reason'       => '고객 요청에 의한 환불', // 환불 사유
            ]);

            if ($response->status() === 200) {
                // $payment = PaymentModel::where('merchant_uid', $req->merchant_uid)->first();
                // PaymentModel::where('merchant_uid', $req->merchant_uid)->delete();
                // OrderModel::where('ord_id', $payment->ord_id)->delete();
                // OrderItemsModel::where('ord_id', $payment->ord_id)->delete();
                PaymentModel::where('merchant_uid', $req->merchant_uid)->update(['status' => 'R']);

                DB::commit();
                return response()->json([
                    'success' => true,
                    'msg'     => '주문이 취소되었습니다.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'msg'     => '주문 취소 중 오류가 발생했습니다.'
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'msg'     => '주문 취소 중 오류가 발생했습니다.'
            ]);
        }
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : getToken()
    //
    // param    : 없음
    //
    // return   : string - 토큰
    //
    // 설명     : 아임포트 환불시 필요한 토큰발급
    // -----------------------------------------------------------------------------------  
    private function getToken() {
        $result  = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post('https://api.iamport.kr/users/getToken', [
            'imp_key'       => '8117658714750626',
            'imp_secret'    => 'sOcpvVruTxXeQ7p1k0NRPyphuqDgZxKFfCuSX1vkSpMC3B46rQEzEzXGaADpdeoHHj1bC3DzWwQSMaXD',
        ]);
        $arr_result = json_decode($result, true);
            return $arr_result["response"]["access_token"];
        }
}
