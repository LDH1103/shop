<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;
use App\Models\User;
use App\Models\ProductModel;
use App\Models\CategoriesModel;

class SellerController extends BaseController
{
    // -----------------------------------------------------------------------------------
    // 함수명   : __construct()
    // 설명     : SellerController 생성자
    //            판매자가 아닌 사용자는 접근하지 못하도록 ifNotSeller 미들웨어를 적용
    //
    // param    : 없음
    //
    // return   : 없음
    // -----------------------------------------------------------------------------------
    public function __construct()
    {
        $this->middleware('ifNotSeller');
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : seller()
    // 설명     : 판매자의 상품 목록 페이지를 반환하는 함수
    //            현재 로그인된 판매자의 상품을 조회하여 페이지네이션으로 반환
    //            각 상품의 가격을 형식화하여 표시하고, 상품 설명 이미지를 asset() 경로로 변환
    //
    // param    : 없음
    //
    // return   : View - 판매자 상품 목록 페이지 (각 상품의 상세 정보 포함)
    // -----------------------------------------------------------------------------------
    public function seller() {
        $seller = Auth::user()->user_id;

        $products = 
            ProductModel::with('category')
            ->where('user_id', $seller)
            ->orderBy('created_at', 'desc')
            ->paginate(10); // 한페이지 10개 페이지네이션

        // 가격을 형식화하여 새로운 속성으로 추가
        foreach ($products as $product) {
            $product->formatted_price = number_format($product->price); // 형식화
        }

        foreach ($products as $product) {
            $images = array_map(function($image) {
                return asset($image); // 이미지 경로를 asset()으로 처리
            }, json_decode($product->description, true)); // JSON을 배열로 변환

            // assets 배열을 JSON 문자열로 변환하여 저장
            $product->description = json_encode($images);
        }

        // $products->dd();
        return view('seller', compact('products'));
    }
}
