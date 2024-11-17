@extends('layouts.app')

@section('content')
<div class="container mt-5" id="shop_div">
    <h2 id="shop_h2">상품등록</h2>

    <form method="POST" action="{{ route('products.create') }}" enctype="multipart/form-data">
        @csrf
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
        <div class="form-group">
            <label for="category" class="form-label" id="shop_label">카테고리</label>
            <select class="form-control" id="category" name="cat_id" required value="{{ old('cat_id') }}">
                @foreach ($categories as $category)
                    <option value="{{ $category->cat_id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('cat_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="name" id="shop_label">상품명</label>
            <input type="text" class="form-control" id="name" name="name" required autofocus value="{{ old('name') }}">
            @error('name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="price" id="shop_label">가격</label>
            <input type="text" class="form-control" id="product-price" name="price" required min="1" value="{{ old('price') }}">
            <div id="resultDiv"></div>
            @error('price')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- 썸네일 이미지 등록 -->
        <div class="form-group">
            <label for="img" id="shop_label">썸네일 이미지</label>
            <input type="file" class="form-control-file" id="img" name="img" accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml" onchange="previewImage(event)">
            @error('img')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- 썸네일 이미지 미리 보기 -->
        <div class="form-group">
            <img id="imgPreview" src="#" alt="이미지 미리보기" style="display: none; max-width: 100%; height: auto;">
        </div>

        <!-- 상품 설명 이미지 -->
        <div class="form-group">
            <label for="descriptionImages" id="shop_label">상품 설명 이미지 (최대 5개)</label>
            <input type="file" class="form-control-file" id="descriptionImages" name="descriptionImages[]" accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml" multiple onchange="previewDescriptionImages(event)">
            @error('descriptionImages')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        
        <!-- 상품 설명 이미지 미리 보기 -->
        <div id="descriptionImagesPreview" class="d-flex overflow-auto" style="white-space: nowrap; max-width: 100%;"></div>
        
        <button type="submit" class="btn btn-primary">상품 등록</button>
    </form>
</div>
@endsection

@section('js')
<script src="{{ asset('js/productCreate.js') }}"></script>
@endsection