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
            @error('pw')
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
                    {{-- <img src="{{ asset('social_login/google.png') }}" alt="Google 로그인" onclick="location.href='{{ route('social.login', 'google') }}'" class="socialIcon"> --}}
                    <img src="{{ asset('social_login/google.png') }}" alt="Google 로그인" onclick="showAlert('구글 로그인 기능은<br>localhost 환경에서는 구현했었으나,<br>도메인 없이 사용할 수 없어<br>현재 제한되었습니다.');" class="socialIcon">
                </div>
            </div>
        </div>
    </form>
    <div class="mt-3">
        계정이 없으신가요? <a href="{{ route('users.register') }}">가입하기</a>
    </div>
</div>
<div class="container" style="font-size: 16px; line-height: 1.8; text-align: center; margin-top: 20px;">
    <div class="mb-3" style="font-size: 14px;">
        <strong>테스트용 계정</strong>
        <p>
            테스트 과정에서 비밀번호가 변경될 수 있습니다.
            <br>
            로그인이 불가능할 경우, 회원가입 후 테스트를 진행해 주세요.
            <br>
            비회원도 구매, 장바구니 이용 및 주문 조회가 가능합니다.
        </p>
    </div>
    <table class="table table-bordered" style="width: auto; margin: 0 auto; font-size: 12px;">
        <thead>
            <tr>
                <th>역할</th>
                <th>아이디</th>
                <th>비밀번호</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>판매자</td>
                <td>seller@test.com</td>
                <td>qwer1234!</td>
            </tr>
            <tr>
                <td>사용자</td>
                <td>user@test.com</td>
                <td>qwer1234!</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection