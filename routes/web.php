<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Middleware\RedirectIfAuthenticated;
use Laravel\Socialite\Facades\Socialite;

// Route::get('/', function () {
//     return view('main');
// })->name('main');
Route::get('/', function () {
    return redirect('/shop/main');
});
Route::get('/shop/main', [ProductController::class, 'main'])->name('main');

// 소셜 로그인 ------------------------------------------------------------
Route::get('auth/{provider}', [UserController::class, 'redirect'])->name('social.login');
Route::get('auth/{provider}/callback', [UserController::class, 'handleCallback']);
// 소셜 로그인 ------------------------------------------------------------

// 상품 --------------------------------------------------------------
Route::get('/products/get', [ProductController::class, 'productGet']);// 메인페이지 카테고리
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');//검색
Route::get('/products/detail/{id}', [ProductController::class, 'detail'])->name('products.detail');// 상세 페이지
Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');// 상품 수정
Route::delete('/products/delete', [ProductController::class, 'delete'])->name('products.delete');// 상품 삭제
// 상품 --------------------------------------------------------------

// 회원 --------------------------------------------------------------
Route::get('/users/login', [UserController::class, 'login'])->name('users.login'); // 로그인 페이지
Route::get('/users/register', [UserController::class, 'register'])->name('users.register'); // 회원가입 페이지
Route::post('/users/register', [UserController::class, 'registerPost']);// 회원 가입
Route::post('/users/login', [UserController::class, 'loginPost']);// 로그인
Route::post('/users/logout', [UserController::class, 'logout'])->name('users.logout');// 로그아웃
Route::get('/users/mypage', [UserController::class, 'mypage'])->name('users.mypage'); // 마이페이지
Route::post('/users/mypage/verify', [UserController::class, 'verifyPassword'])->name('users.mypage.verify'); // 마이페이지 접근시 비밀번호 재확인
Route::post('/users/addAddress', [UserController::class, 'addAddress'])->name('users.addAddress');// 배송지 추가
Route::put('/users/editAddress', [UserController::class, 'editAddress'])->name('users.editAddress');// 배송지 수정
Route::delete('/users/deleteAddress', [UserController::class, 'deleteAddress'])->name('users.deleteAddress');// 배송지 삭제
// 회원 --------------------------------------------------------------

// 판매자 -------------------------------------------------------------
Route::get('/sellers/main', [SellerController::class, 'seller'])->name('sellers.main');// 판매자 페이지
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');// 상품등록 페이지
Route::post('/products/create', [ProductController::class, 'createPost'])->name('products.create');// 상품등록
// 판매자 -------------------------------------------------------------

// 장바구니 --------------------------------------------------------------
Route::get('/carts/mycart', [CartController::class, 'myCart'])->name('carts.myCart');// 장바구니
Route::post('/carts/add', [CartController::class, 'addCart'])->name('carts.addCart');// 장바구니에 담기
Route::post('/carts/update', [CartController::class, 'uptCart'])->name('carts.uptCart');// 장바구니 수정  
Route::delete('/carts/delete', [CartController::class, 'delCart'])->name('carts.delCart');// 장바구니 삭제
// 장바구니 --------------------------------------------------------------

// 구매 --------------------------------------------------------------
Route::post('/orders/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');// 결제 페이지
// 구매 --------------------------------------------------------------


Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
