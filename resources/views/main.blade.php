@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{asset('css/main.css')}}">
@endsection

@section('content')
<div class="container mt-4" style="max-width: 600px;">
    <form action="{{ route('products.search') }}" method="GET">
        <div class="input-group mb-3 mx-auto">
            <!-- 카테고리 선택 -->
            <select id="category_search" name="category" class="form-select" style="max-width: 100px; margin-right: 10px;">
                <option value="0">전체</option>
                @foreach($categories as $category)
                    <option value="{{ $category->cat_id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <!-- 검색창 -->
            <input type="text" class="form-control" name="query" placeholder="찾고 싶은 상품을 검색해보세요!" aria-label="상품 검색" aria-describedby="searchButton">

            <!-- 검색 버튼 (돋보기 아이콘) -->
            <button class="btn btn-outline-secondary" type="submit" id="searchButton">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>
</div>
<div id="carouselIndicators" class="carousel carousel-dark slide" data-ride="carousel">
    <ol class="carousel-indicators">
        <li data-target="#carouselIndicators" data-slide-to="0" class="active"></li>
        <li data-target="#carouselIndicators" data-slide-to="1"></li>
        <li data-target="#carouselIndicators" data-slide-to="2"></li>
    </ol>
    <div class="carousel-inner">
        <div class="carousel-item active" data-bs-interval="20000">
            <img src="{{ asset('fac_img/slide1.png') }}" class="d-block w-100" alt="Slide 1">
        </div>
        <div class="carousel-item">
            <img src="{{ asset('fac_img/slide2.jpg') }}" class="d-block w-100" alt="Slide 2">
        </div>
        <div class="carousel-item">
            <img src="{{ asset('fac_img/slide3.jpg') }}" class="d-block w-100" alt="Slide 3">
        </div>
    </div>
    <a class="carousel-control-prev" href="#carouselIndicators" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#carouselIndicators" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
</div>
<div class="container">
    <h2 class="shopTitle">상품 목록</h2>
    <!-- 카테고리 -->
    <div class="mb-3">
        <select id="category_select" class="form-select">
            <option value="0">전체</option>
            @foreach($categories as $category)
            <option value="{{ $category->cat_id }}">{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- 로딩중 표시 --}}
    <div id="loading" style="display: none;">
        <div class="loading-background">
            <img src="https://i.gifer.com/ZZ5H.gif" alt="Loading..." width="50">
        </div>
    </div>

    <div class="row" id="product_list_container">
        @if($products->isEmpty())
            등록된 상품이 없습니다.
        {{-- @else
            @foreach($products as $product)
                <div class="col-md-3 product_list">
                    <div class="card mb-3" onclick="window.location='{{ route('products.detail', ['id' => $product->pro_id]) }}';" style="cursor: pointer;">
                        <img src="{{ asset($product->img) }}" class="card-img-top" alt="{{ $product->name }}" id="card_img">
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text">{{ number_format($product->price) }} 원</p>
                        </div>
                    </div>
                </div>
            @endforeach --}}
        @endif
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('js/main.js') }}"></script>
@endsection