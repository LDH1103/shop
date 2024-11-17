@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{asset('css/review.css')}}">
@endsection

@section('content')
<h2 class="text-center mb-4 shopTitle" style="margin-top: 30px;">리뷰 작성 페이지</h2>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <h3 class="product-title">{{ $product->name }}</h3>
            <img src="{{ asset($product->img) }}" alt="상품 이미지" class="product-thumbnail img-fluid rounded shadow">
        </div>
    </div>
    <form action="{{ route('reviews.create') }}" method="post" class="review-form mt-4">
        @csrf
        <div class="rating-container text-center">
            <div class="rating">
                <!-- 0.5점 -->
                <label class="rating__label rating__label--half" for="starhalf">
                    <input type="radio" id="starhalf" class="rating__input" name="rating" value="0.5"
                        {{ old('rating') == '0.5' ? 'checked' : '' }}>
                    <span class="star-icon"></span>
                </label>
            
                <!-- 1점 -->
                <label class="rating__label rating__label--full" for="star1">
                    <input type="radio" id="star1" class="rating__input" name="rating" value="1"
                        {{ old('rating') == '1' ? 'checked' : '' }}>
                    <span class="star-icon"></span>
                </label>
            
                <!-- 1.5점 -->
                <label class="rating__label rating__label--half" for="star1half">
                    <input type="radio" id="star1half" class="rating__input" name="rating" value="1.5"
                        {{ old('rating') == '1.5' ? 'checked' : '' }}>
                    <span class="star-icon"></span>
                </label>
            
                <!-- 2점 -->
                <label class="rating__label rating__label--full" for="star2">
                    <input type="radio" id="star2" class="rating__input" name="rating" value="2"
                        {{ old('rating') == '2' ? 'checked' : '' }}>
                    <span class="star-icon"></span>
                </label>
            
                <!-- 2.5점 -->
                <label class="rating__label rating__label--half" for="star2half">
                    <input type="radio" id="star2half" class="rating__input" name="rating" value="2.5"
                        {{ old('rating') == '2.5' ? 'checked' : '' }}>
                    <span class="star-icon"></span>
                </label>
            
                <!-- 3점 -->
                <label class="rating__label rating__label--full" for="star3">
                    <input type="radio" id="star3" class="rating__input" name="rating" value="3"
                        {{ old('rating') == '3' ? 'checked' : '' }}>
                    <span class="star-icon"></span>
                </label>
            
                <!-- 3.5점 -->
                <label class="rating__label rating__label--half" for="star3half">
                    <input type="radio" id="star3half" class="rating__input" name="rating" value="3.5"
                        {{ old('rating') == '3.5' ? 'checked' : '' }}>
                    <span class="star-icon"></span>
                </label>
            
                <!-- 4점 -->
                <label class="rating__label rating__label--full" for="star4">
                    <input type="radio" id="star4" class="rating__input" name="rating" value="4"
                        {{ old('rating') == '4' ? 'checked' : '' }}>
                    <span class="star-icon"></span>
                </label>
            
                <!-- 4.5점 -->
                <label class="rating__label rating__label--half" for="star4half">
                    <input type="radio" id="star4half" class="rating__input" name="rating" value="4.5"
                        {{ old('rating') == '4.5' ? 'checked' : '' }}>
                    <span class="star-icon"></span>
                </label>
            
                <!-- 5점 -->
                <label class="rating__label rating__label--full" for="star5">
                    <input type="radio" id="star5" class="rating__input" name="rating" value="5"
                        {{ old('rating') == '5' ? 'checked' : '' }}>
                    <span class="star-icon"></span>
                </label>
            </div>
        </div>
        @error('rating')
            <small class="text-danger">{{ $message }}</small>
        @enderror
        <div class="form-group mt-4">
            <textarea name="content" id="content" class="form-control" rows="5" placeholder="리뷰를 작성해주세요" value="{{ old('content') }}"></textarea>
            @error('content')
                <small class="text-danger">{{ $message }}</small>
            @enderror
            <input type="hidden" name="pro_id" value="{{ $product->pro_id }}">
            <input type="hidden" name="user_id" value="{{ Auth::user()->user_id }}">
            <input type="hidden" name="ord_id" value="{{ $ord_id }}">
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-outline-primary mt-3">리뷰 작성</button>
        </div>
    </form>
</div>
@endsection

@section('js')
<script src="{{ asset('js/review.js') }}"></script>
@endsection
