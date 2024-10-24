<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\CartModel;
use App\Models\ProductModel;

class OrderController extends Controller
{
    public function checkout(Request $req) {
        // 선택된 상품 목록을 받아옴
        $selectedItems = $req->items;

        // JSON 형태로 받아온 데이터를 PHP 배열로 변환
        $items = array_map(function ($item) {
            return json_decode($item, true);
        }, $selectedItems);

        // 선택된 상품 데이터를 결제 페이지로 전달
        return view('checkout')->with('items', $items);
    }
}
