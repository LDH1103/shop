@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="container mt-5" id="shop_div">
    <h2 id="shop_h2">회원가입</h2>

    <form method="POST" action="{{ route('users.register') }}">
        @csrf

        <div class="form-group">
            <label for="name">이름</label>
            <input type="text" class="form-control" id="name" name="name" required autofocus value="{{ old('name') }}">
            @error('name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">이메일</label>
            <input type="email" class="form-control" id="email" name="email" required value="{{ old('email') }}">
            @error('email')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">비밀번호</label>
            <input type="password" class="form-control" id="password" name="password" required placeholder="8 ~ 20자, 알파벳, 숫자, 특수문자를 모두 포함해 주세요">
            @error('password')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirm">비밀번호 확인</label>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
            @error('password_confirm')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="seller">판매자로 가입</label>
            <input type="checkbox" id="seller" name="seller">
            @error('seller')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">회원가입</button>
    </form>

    <div class="mt-3">
        이미 계정이 있으신가요? <a href="{{ route('users.login') }}">로그인하기</a>
    </div>
</div>
@endsection