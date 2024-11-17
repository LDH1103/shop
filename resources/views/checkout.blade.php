@extends('layouts.app')

@section('title', '결제')

@section('css')
<link rel="stylesheet" href="{{asset('css/checkout.css')}}">
@endsection

@section('content')
<div class="container mt-4">
    <h2 class="shopTitle">결제 페이지</h2>
    <h4>배송지 정보</h4>
    @if($defaultAddress)
        <!-- 기본 배송지 출력 -->
        <div id="defaultAddressCard" class="card mb-3">
            <div class="card-body">
                <h5 id="recipient" class="card-title">{{ $defaultAddress->recipient }}</h5>
                <span id="postcode" class="card-text d-block">{{ $defaultAddress->postcode }}</span>
                <span id="address" class="card-text d-block">
                    {{ $defaultAddress->address }}, {{ $defaultAddress->detailAddress }}
                    @if($defaultAddress->extraAddress)
                        , {{ $defaultAddress->extraAddress }}
                    @endif
                </span>
                <span id="phone" class="card-text d-block">{{ $defaultAddress->phone }}</span>
                <span id="defaultLabel" class="card-text d-block"><strong>기본 배송지</strong></span>
            </div>
            <!-- 배송지 변경 버튼 -->
            <button class="btn btn-outline-secondary mt-3" data-toggle="modal" data-target="#addressModal">배송지 변경</button>
        </div>

        <!-- 배송지 선택 모달 -->
        <div class="modal fade" id="addressModal" tabindex="-1" role="dialog" aria-labelledby="addressModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addressModalLabel">배송지 선택</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- 기존 주소 목록 -->
                        <div id="addressList">
                        </div>

                        <!-- 새 주소 추가 버튼 -->
                        <button class="btn btn-outline-primary w-100 mt-3" id ="addBtn" onclick="toggleAddAddressForm()">+ 새 주소 추가하기</button>
                        
                        <!-- 새 주소 추가 폼 -->
                            <form id="addAddressForm" method="POST">
                                @csrf
                                <!-- 우편번호 및 찾기 버튼 -->
                                <div class="form-group">
                                    <label for="addPostcode">주소</label>
                                    <div class="input-group mb-3">
                                        <input type="text" id="addPostcode" name="postcode" placeholder="우편번호" readonly class="form-control" required>
                                        <div class="input-group-append">
                                            <input type="button" onclick="daumPostcode()" value="우편번호 찾기" class="btn btn-outline-primary">
                                        </div>
                                    </div>
                                </div>
                                <!-- 주소 입력 -->
                                <div class="form-group">
                                    <input type="text" id="addAddress" name="address" placeholder="주소" readonly class="form-control" required>
                                </div>
                                <!-- 상세주소 및 참고항목 -->
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <input type="text" id="addDetailAddress" name="detailAddress" placeholder="상세주소" class="form-control" required>
                                        <input type="text" id="addExtraAddress" name="extraAddress" placeholder="참고항목" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="addRrecipient">수령인</label>
                                    <input type="text" class="form-control" id="addRecipient" name="recipient" required>
                                </div>
                                <div class="form-group">
                                    <label for="addPhone">전화번호</label>
                                    <input type="text" class="form-control" id="addPhone" name="phone" required>
                                </div>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="addDefault" name="default">
                                        <label class="form-check-label" for="addDefault">기본 배송지로 저장</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-outline-primary w-100">저장</button>
                                </div>
                            </form>
                        </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- 비로그인 상태 또는 배송지 정보가 없는 경우 -->
        <div class="addAddress">
                <div class="form-group">
                    <label for="addPostcode">주소</label>
                    <div class="input-group mb-3">
                        <input type="text" id="addPostcode" name="addPostcode" placeholder="우편번호" readonly class="form-control" required>
                        <div class="input-group-append">
                            <input type="button" onclick="daumPostcode()" value="우편번호 찾기" class="btn btn-outline-primary">
                        </div>
                    </div>
                </div>
                <!-- 주소 입력 -->
                <div class="form-group">
                    <input type="text" id="addAddress" name="addAddress" placeholder="주소" readonly class="form-control" required>
                </div>
                <!-- 상세주소 및 참고항목 -->
                <div class="form-group">
                    <div class="input-group mb-3">
                        <input type="text" id="addDetailAddress" name="addDetailAddress" placeholder="상세주소" class="form-control" required>
                        <input type="text" id="addExtraAddress" name="addExtraAddress" placeholder="참고항목" class="form-control" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label for="addRecipient">수령인</label>
                    <input type="text" class="form-control" id="addRecipient" name="addRecipient" required>
                </div>
                <div class="form-group">
                    <label for="addPhone">전화번호</label>
                    <input type="text" class="form-control" id="addPhone" name="addPhone" required>
                </div>
        </div>
    @endif

    <h4>상품 정보</h4>
    <hr>
    @foreach($items as $item)
            <div class="cart-item d-flex justify-content-between align-items-center" data-product-id="{{ $item['id'] }}">
                <div class="d-flex align-items-center">
                    <img src="{{ asset($item['img']) }}" alt="{{ $item['name'] }}" style="width: 60px; height: 60px;">
                    <div class="ml-3">
                        <p>{{ $item['name'] }}</p>
                        <p>총 {{ number_format($item['totalPrice']) }} 원</p>
                    </div>
                </div>
                <div class="d-flex align-items-center ml-auto">
                    <div class="btnBox d-flex align-items-center">
                        <!-- 수량 표시 -->
                        <div type="text" class="quantity">
                            {{ $item['quantity'] }} 개
                        </div>
                    </div>
                </div>
            </div>
            <hr>
    @endforeach

    <div id="checkoutDiv">
        <!-- 총 결제 금액 -->
        <h3>
            총 결제 금액: {{ number_format($totalAmount); }}원
        </h3>
    
        <!-- 결제하기 버튼 -->
        <form action="" method="POST" style="display: inline;">
            @csrf
            <button type="button" class="btn btn-outline-primary" onclick="requestPay({{ $totalAmount }})">결제하기</button>
        </form>
    </div>
</div>

<!-- 사용자 정보를 JS에 전달 -->
<span id="buyerEmail" data-value="{{ Auth::check() ? Auth::user()->email : '비회원' }}" style="display: none;"></span>
<span id="buyerName" data-value="{{ Auth::check() ? Auth::user()->name : '비회원' }}" style="display: none;"></span>
<span id="productsName" data-value="{{ $productNames }}" style="display: none;"></span>
<span id="addressId" data-value="{{ $defaultAddress->add_id ?? 0 }}" style="display: none;"></span>
@endsection

@section('js')
<script src="{{ asset('js/checkout.js') }}"></script>
<script>
    // PHP에서 전달된 items 배열을 JSON 형태로 JavaScript 변수에 저장
    const items = @json($items);
</script>
@endsection