@extends('layouts.app')

@section('title', "$query - 검색")

@section('css')
<link rel="stylesheet" href="{{asset('css/search.css')}}">
@endsection

@section('content')
<div class="container mt-4">
    <form action="{{ route('products.search') }}" method="GET">
        <div class="input-group mb-3 mx-auto" style="max-width: 600px;">
            <!-- 카테고리 선택 -->
            <select id="category_search" name="category" class="form-select" style="max-width: 100px; margin-right: 10px;">
                <option value="0" {{ $selectedCat == 0 ? 'selected' : '' }}>전체</option>
                @foreach($categories as $category)
                    <option value="{{ $category->cat_id }}" {{ $selectedCat == $category->cat_id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <!-- 검색창 -->
            <input type="text" class="form-control" name="query" placeholder="찾고 싶은 상품을 검색해보세요!" aria-label="상품 검색" aria-describedby="searchButton" value="{{ $query }}">

            <!-- 검색 버튼 (돋보기 아이콘) -->
            <button class="btn btn-outline-secondary" type="submit" id="searchButton">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>
</div>

<div class="container mt-4">
    <p>'<strong>{{ $query }}</strong>'에 대한 검색 결과</p>
    <div class="row">
        @if($products->isEmpty())
            <div class="col-12 text-center">
                <h5>
                    검색결과가 없습니다.
                    <br>
                    다른 검색어를 입력하시거나 철자와 띄어쓰기를 확인해 보세요.
                </h5>
            </div>
        @else
            @foreach($products as $product)
                <div class="col-md-3">
                    <div class="card mb-4" onclick="window.location='{{ route('products.detail', ['id' => $product->pro_id]) }}';" style="cursor: pointer;">
                        <img src="{{ asset($product->img) }}" class="card-img-top" alt="{{ $product->name }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text">{{ number_format($product->price) }} 원</p>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- 페이지네이션 -->
    @if(!$products->isEmpty())
        <div class="pagination-container d-flex justify-content-center mt-4 mb-4">
            {{ $products->appends(request()->query())->links('vendor.pagination.custom') }}
        </div>
    @endif
</div>
@endsection

@section('js')
{{-- <script src="{{ asset('js/.js') }}"></script> --}}
@endsection