@extends('layouts.app')

@section('title', '마이페이지')

@section('css')
<link rel="stylesheet" href="{{asset('css/mypage.css')}}">
@endsection

@section('content')
<div class="container mt-4">
    <h2 class="shopTitle">마이페이지</h2>
    {{-- @if (!session('pwConfirmed'))
    <div class="container mt-4" id="shop_div">
        <form method="POST" action="{{ route('users.mypage.verify') }}">
            @csrf
            <div class="form-group">
                <label for="password">비밀번호</label>
                <input type="password" class="form-control" id="pw" name="pw" required>
                @error('pw')
                <div class="alert alert-danger mt-2">
                    {{ $message }}
                </div>
            @enderror
            </div>
            <div class="text-right">
                <button type="submit" class="btn btn-primary">확인</button>
            </div>
        </form>
    </div>
    @else --}}
    <div class="container mt-4 tab-container">
        <div class="row">
            <div class="col-md-3">
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <a class="nav-link active" id="v-pills-order-history-tab" data-toggle="pill" href="#v-pills-order-history" role="tab" aria-controls="v-pills-order-history" aria-selected="false">주문 내역</a>
                    <a class="nav-link" id="v-pills-user-info-tab" data-toggle="pill" href="#v-pills-user-info" role="tab" aria-controls="v-pills-user-info" aria-selected="true">내정보</a>
                    <a class="nav-link" id="v-pills-shipping-info-tab" data-toggle="pill" href="#v-pills-shipping-info" role="tab" aria-controls="v-pills-shipping-info" aria-selected="false">배송지 관리</a>
                    <a class="nav-link" id="v-pills-other-settings-tab" data-toggle="pill" href="#v-pills-other-settings" role="tab" aria-controls="v-pills-other-settings" aria-selected="false">기타 설정</a>
                </div>
            </div>
            <div class="col-md-9 tab-line">
                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade show active" id="v-pills-order-history" role="tabpanel" aria-labelledby="v-pills-order-history-tab">
                        <p>주문 내역</p>
                    </div>
                    <div class="tab-pane fade" id="v-pills-user-info" role="tabpanel" aria-labelledby="v-pills-user-info-tab">
                        <p>내정보</p>
                    </div>
                    <div class="tab-pane fade" id="v-pills-shipping-info" role="tabpanel" aria-labelledby="v-pills-shipping-info-tab">
                        @if($addresses->isEmpty())
                            배송지가 없습니다.
                        @else
                            <div id="addressList">
                                @foreach($addresses as $address)
                                <div class="address-card">
                                    <div class="address-details">
                                        <span>{{ $address->recipient }}</span><br>
                                        <span>{{ $address->address . ', ' . $address->detailAddress}}</span><br>
                                        <span>{{ $address->phone }}</span><br>
                                        <span>{{ $address->default ? '기본 배송지' : '' }}</span>
                                        <span class="hidden-address hidden-postcode" data-postcode="{{ $address->postcode }}"></span>
                                        <span class="hidden-address hidden-extraAddress" data-extraAddress="{{ $address->extraAddress }}"></span>
                                        <span class="hidden-address hidden-addressPk" data-addressPk="{{ $address->add_id }}"></span>
                                    </div>
                                    <div class="address-actions">
                                        <button data-id="{{ $address->id }}" class="btn btn-outline-primary btn-edit">수정</button>
                                        <button data-id="{{ $address->id }}" class="btn btn-outline-danger btn-delete">삭제</button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary btn-block" data-toggle="modal" data-target="#addAddressModal">
                                배송지 추가하기
                            </button>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-other-settings" role="tabpanel" aria-labelledby="v-pills-other-settings-tab">
                        <p>기타</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 배송지 추가 모달 -->
    <div class="modal fade" id="addAddressModal" tabindex="-1" role="dialog" aria-labelledby="addAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAddressModalLabel">배송지 추가하기</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addAddressForm" method="POST" action="{{ route('users.addAddress') }}">
                        @csrf
                        <!-- 우편번호 및 찾기 버튼 -->
                        <div class="form-group">
                            <label for="postcode">주소</label>
                            <div class="input-group mb-3">
                                <input type="text" id="postcode" name="postcode" placeholder="우편번호" readonly class="form-control" required>
                                <div class="input-group-append">
                                    <input type="button" onclick="daumPostcode()" value="우편번호 찾기" class="btn btn-outline-primary">
                                </div>
                            </div>
                        </div>
                        <!-- 주소 입력 -->
                        <div class="form-group">
                            <input type="text" id="address" name="address" placeholder="주소" readonly class="form-control" required>
                        </div>
                        <!-- 상세주소 및 참고항목 -->
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <input type="text" id="detailAddress" name="detailAddress" placeholder="상세주소" class="form-control" required>
                                <input type="text" id="extraAddress" name="extraAddress" placeholder="참고항목" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="recipient">수령인</label>
                            <input type="text" class="form-control" id="recipient" name="recipient" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">전화번호</label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="default" name="default">
                                <label class="form-check-label" for="default">기본 배송지로 저장</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                            <button type="submit" class="btn btn-primary">저장</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 배송지 수정 모달 -->
    <div class="modal fade" id="editAddressModal" tabindex="-1" role="dialog" aria-labelledby="editAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAddressModalLabel">배송지 수정하기</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editAddressForm" method="POST" action="{{ route('users.editAddress') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_address_id" name="address_id">
                        <!-- 우편번호 및 찾기 버튼 -->
                        <div class="form-group">
                            <label for="edit_postcode">주소</label>
                            <div class="input-group mb-3">
                                <input type="text" id="edit_postcode" name="postcode" placeholder="우편번호" readonly class="form-control" required>
                                <div class="input-group-append">
                                    <input type="button" onclick="daumPostcode()" value="우편번호 찾기" class="btn btn-outline-primary">
                                </div>
                            </div>
                        </div>
                        <!-- 주소 입력 -->
                        <div class="form-group">
                            <input type="text" id="edit_address" name="address" placeholder="주소" readonly class="form-control" required>
                        </div>
                        <!-- 상세주소 및 참고항목 -->
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <input type="text" id="edit_detailAddress" name="detailAddress" placeholder="상세주소" class="form-control" required>
                                <input type="text" id="edit_extraAddress" name="extraAddress" placeholder="참고항목" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_recipient">수령인</label>
                            <input type="text" class="form-control" id="edit_recipient" name="recipient" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_phone">전화번호</label>
                            <input type="text" class="form-control" id="edit_phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="edit_default" name="default">
                                <label class="form-check-label" for="edit_default">기본 배송지로 저장</label>
                            </div>
                            <input type="hidden" id="edit_addId" name="addId" value="">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                            <button type="submit" class="btn btn-primary">저장</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('js/mypage.js') }}"></script>
@endsection