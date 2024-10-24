@extends('layouts.app')

@section('title', $product->name . ' - 상세페이지')

@section('css')
<link rel="stylesheet" href="{{asset('css/detail.css')}}">
@endsection

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <img src="{{ asset($product->img) }}" alt="{{ $product->name }}" class="product-image">
        </div>
        <div class="col-md-6">
            <h1 class="product-title">{{ $product->name }}</h1>
            <p class="price">₩ {{ number_format($product->price) }}</p>
            <!-- 수량 선택 -->
            <form action="{{ route('carts.addCart') }}" method="POST">
                @csrf
                <input type="hidden" name="proId" id="proId" value="{{ $product->pro_id }}">
                <div class="form-group">
                    <input type="number" id="quantity" name="quantity" value="1" min="1" class="form-control" style="width: 100px;">
                </div>
                <button class="btn btn-outline-primary" id="addCartBtn">장바구니에 담기</button>
                <button class="btn btn-primary">바로 구매</button>
            </form>            
        </div>
    </div>
    <hr>
    <div class="description">
        @foreach(json_decode($product->description) as $img)
            <img src="{{ asset($img) }}" class="card-img-top description-img" alt="{{ $img }}">
        @endforeach
    </div>
    <hr>
    <div class="related-products">
        <h4>추천 상품</h4>
        <div class="product-scroll-container" id="productScrollContainer">
            @foreach($relatedProducts as $relatedProduct)
                <div class="product-card" onclick="window.location='{{ route('products.detail', ['id' => $relatedProduct->pro_id]) }}';" style="cursor: pointer;">
                    <img src="{{ asset($relatedProduct->img) }}" class="card-img-top" alt="{{ $relatedProduct->name }}">
                    <div class="card-body">
                        <h5 class="card-title">{{ $relatedProduct->name }}</h5>
                        <p class="card-text">₩ {{ number_format($relatedProduct->price) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('js/detail.js') }}"></script>
@endsection