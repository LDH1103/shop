<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '쇼핑몰')</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="{{asset('css/shop.css')}}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
    @yield('css')
</head>
<body>
    {{-- 세션에 'alert'가 존재하면 alert창 출력 --}}
    @if(session('alert'))
        <script>
            Swal.fire({
                // text: '{{ session('alert') }}',
                html: '{!! session('alert') !!}',
                icon: 'info',
                confirmButtonText: '확인'
            });
        </script>
        @php
            session()->forget('alert');
        @endphp
    @endif

    <!-- 네비 시작 -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="{{ route('main') }}">쇼핑몰</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
            @if (Auth::check())
                <li class="nav-item">
                    <span class="nav-link" href="#">{{ Auth::user()->name }}님, 반갑습니다.</span>
                </li>
                @if (Auth::user()->admin_flg === '1')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('sellers.main') }}" style="color:red;">판매자 페이지</a>
                    </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('users.mypage') }}">마이페이지</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('users.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">로그아웃</a>
                    <form id="logout-form" action="{{ route('users.logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
            @else
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('orders.lookup') }}">비회원 주문조회</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('users.login') }}">로그인</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('users.register') }}">회원 가입</a>
                </li>
            @endif
            <li class="nav-item">
                <a class="nav-link" href="{{ route('carts.myCart') }}">장바구니</a>
            </li>
            </ul>
        </div>
    </nav>
    <!-- 네비 끝 -->

    <span class="mobile-alert">
        이 프로젝트는 PC 환경에 최적화되어 있습니다.
        <br>
        모바일 환경에서는 정상적으로 동작하지 않을 수 있습니다.
    </span>

    @yield('content')
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- 주소 api -->
    <script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
    <script src="{{ asset('js/common.js') }}"></script>
    <script src="{{ asset('js/sweetAlert.js') }}"></script>
    <!-- 아임포트 api -->
    {{-- <script src="https://cdn.iamport.kr/v1/iamport.js"></script> --}}
    <!-- iamport.payment.js -->
    <script type="text/javascript" src="https://cdn.iamport.kr/js/iamport.payment-1.2.0.js"></script>
    @yield('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
            if (isMobile) {
                document.querySelector('.mobile-alert').style.display = 'block';
            }
        });
    </script>
</body>
</html>
