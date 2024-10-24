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
    public function __construct()
    {
        $this->middleware('ifNotSeller');
    }

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
