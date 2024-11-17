<?php

namespace App\Http\Controllers;

use App\Models\ReviewModel;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller as BaseController;

class ReviewController extends BaseController
{
    // -----------------------------------------------------------------------------------
    // 함수명   : __construct()
    // 설명     : ReviewController 생성자
    //            특정 경로에 대한 미들웨어 적용으로 접근 권한 관리
    //
    // param    : 없음
    //
    // return   : 없음
    // -----------------------------------------------------------------------------------
    public function __construct()
    {
        $this->middleware('checkLogin')->only(['createReviewPage', 'createReview']);
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : createReviewPage()
    // 설명     : 리뷰 작성 페이지를 반환하는 함수
    //
    // param    : Request $req - 상품 ID
    //
    // return   : View - 리뷰 작성 페이지
    // -----------------------------------------------------------------------------------
    public function createReviewPage(Request $req)
    {
        $pro_id  = $req->pro_id;
        $ord_id  = $req->ord_id;
        $product = ProductModel::find($pro_id);

        $reviewExists = ReviewModel::where('ord_id', $req->ord_id)
            ->where('user_id', Auth::user()->user_id)
            ->where('pro_id', $req->pro_id)
            ->exists();

        if ($reviewExists) {
                return redirect()->route('users.mypage')->with('alert', '이미 리뷰를 작성하신 상품입니다.');
            }

        return view('review', compact('product', 'ord_id'));
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : createReview()
    // 설명     : 리뷰 작성 함수
    //
    // param    : Request $req - 리뷰 작성 정보
    //
    // return   : 리뷰 작성 성공 여부
    // -----------------------------------------------------------------------------------
    public function createReview(Request $req)
    {
        $rules = [
            'rating'    => 'required|numeric|min:0.5|max:5',
            'content'   => 'required|string|max:1000',
        ];

        $validator = Validator::make($req->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            ReviewModel::create([
                'user_id'   => $req->user_id,
                'pro_id'    => $req->pro_id,
                'ord_id'    => $req->ord_id,
                'rating'    => $req->rating,
                'comment'   => $req->content,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('alert', '리뷰 작성중 오류가 발생했습니다.');
        }

        return redirect()->route('products.detail', ['id' => $req->pro_id])->with('alert', '리뷰 작성이 완료되었습니다.');
    }
}
