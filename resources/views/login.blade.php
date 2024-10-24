@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{asset('css/login.css')}}">
@endsection

@section('content')
<div class="container mt-5" id="shop_div">
    <h2 id="shop_h2">로그인</h2>

    <form method="POST" action="{{ route('users.login') }}">
        @csrf

        <div class="form-group">
            <label for="email">이메일</label>
            <input type="text" class="form-control" id="email" name="email" required autofocus value="{{ old('email') }}">
            @error('email')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="pw">비밀번호</label>
            <input type="password" class="form-control" id="pw" name="pw" required>
            @error('password')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        @error('login')
            <small class="text-danger">{{ $message }}</small>
        @enderror
        <div class="d-flex justify-content-end mt-3"> <!-- 버튼을 우측으로 정렬 -->
            <button type="submit" class="btn btn-primary">로그인</button>
        </div>
        <br>
        <div class="container mt-4">
            <div class="row justify-content-center">
                <div class="col-auto mx-2">
                    <img src="{{ asset('social_login/kakao.png') }}" alt="Kakao 로그인" onclick="location.href='{{ route('social.login', 'kakao') }}'" class="socialIcon">
                </div>
                <div class="col-auto mx-2">
                    <img src="{{ asset('social_login/naver.png') }}" alt="Naver 로그인" onclick="location.href='{{ route('social.login', 'naver') }}'" class="socialIcon">
                </div>
                <div class="col-auto mx-2">
                    <img src="{{ asset('social_login/google.png') }}" alt="Google 로그인" onclick="location.href='{{ route('social.login', 'google') }}'" class="socialIcon">
                </div>
            </div>
        </div>
    </form>
    <div class="mt-3">
        계정이 없으신가요? <a href="{{ route('users.register') }}">가입하기</a>
    </div>
</div>
@endsection