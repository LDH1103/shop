@extends('layouts.app')

@section('title', '장바구니')

@section('css')
<link rel="stylesheet" href="{{asset('css/myCart.css')}}">
@endsection

@section('content')
<div class="container mt-4">
    <h2 class="shopTitle">장바구니</h2>
    <div class="cartContainer mb-3">
        @if(Auth::check())
            @if($cartItems->isEmpty())
                <p>장바구니가 비어 있습니다.</p>
            @else
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <input type="checkbox" class="cartCheck" id="selectAll" onchange="selectAll(this); calculateTotal();">
                        <label for="selectAll">전체선택</label>
                    </div>
                    <button class="btn btn-outline-danger" onclick="delSelectedItems()">선택 항목 삭제</button>
                </div>
                @foreach($cartItems as $item)
                <div class="cart-item d-flex justify-content-between align-items-center" data-product-id="{{ $item->product->pro_id }}">
                    <input type="checkbox" class="product-checkbox" data-product-id="{{ $item->product->pro_id }}" onchange="updateSelectAllCart(); calculateTotal();" style="margin-right: 5px;">
                    <div class="d-flex align-items-center">
                        <img src="{{ asset($item->product->img) }}" alt="{{ $item->product->name }}" style="width: 60px; height: 60px;">
                        <div class="ml-3">
                            <span onclick="location.href='/products/detail/{{ $item->product->pro_id }}'" style="cursor: pointer;">{{ $item->product->name }}</span>
                            <p>{{ number_format($item->product->price) }} 원</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center ml-auto">
                        <div class="btnBox d-flex align-items-center"> <!-- 버튼 박스 -->
                            <div class="quantityBtn minus" onclick="changeQuantity(this, -1)">-</div>
                            <input type="text" class="quantity" value="{{ $item->quantity }}" style="width: 50px; text-align: center;" oninput="validateInput(this); calculateTotal();">
                            <div class="quantityBtn" onclick="changeQuantity(this, 1)">+</div>
                        </div>
                        <button class="btn btn-outline-danger ml-2" onclick="delBtn({{ $item->product->pro_id }})">삭제</button> <!-- 삭제 버튼 -->
                    </div>
                </div>
                @endforeach

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="total-amount">
                        <h5>예상 금액: <span id="totalAmount">0 원</span></h5>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="goToCheckout()">구매하기</button>
                    </div>
                </div>
            @endif
        @else
            @if (!empty($cartItems))
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <input type="checkbox" class="cartCheck" id="selectAll" onchange="selectAll(this); calculateTotal();">
                        <label for="selectAll">전체선택</label>
                    </div>
                    <button class="btn btn-outline-danger" onclick="delSelectedItems()">선택 항목 삭제</button>
                </div>
                @foreach ($cartItems as $item)
                    <div class="cart-item d-flex justify-content-between align-items-center" data-product-id="{{ $item['product']->pro_id }}">
                        <input type="checkbox" class="product-checkbox" data-product-id="{{ $item['product']->pro_id }}" onchange="updateSelectAllCart(); calculateTotal();" style="margin-right: 5px;">
                        <div class="d-flex align-items-center">
                            <img src="{{ asset($item['product']->img) }}" alt="{{ $item['product']->name }}" style="width: 60px; height: 60px;">
                            <div class="ml-3">
                                <span onclick="location.href='/products/detail/{{ $item['product']->pro_id }}'" style="cursor: pointer;">{{ $item['product']->name }}</span>
                                <p>{{ number_format($item['product']->price) }} 원</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center ml-auto">
                            <div class="btnBox d-flex align-items-center"> <!-- 버튼 박스 -->
                                <div class="quantityBtn minus" onclick="changeQuantity(this, -1)">-</div>
                                <input type="text" class="quantity" value="{{ $item['quantity'] }}" style="width: 50px; text-align: center;" oninput="validateInput(this); calculateTotal();">
                                <div class="quantityBtn" onclick="changeQuantity(this, 1)">+</div>
                            </div>
                            <button class="btn btn-outline-danger ml-2" onclick="delBtn({{ $item['product']->pro_id }})">삭제</button> <!-- 삭제 버튼 -->
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="total-amount">
                        <h5>예상 금액: <span id="totalAmount">0 원</span></h5>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="goToCheckout()">구매하기</button>
                    </div>
                </div>
            @else
                <p>장바구니가 비어 있습니다.</p>
            @endif
        @endif
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('js/myCart.js') }}"></script>
@endsection
