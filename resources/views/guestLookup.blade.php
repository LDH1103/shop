@extends('layouts.app')

{{-- @section('title', '') --}}

@section('css')
<link rel="stylesheet" href="{{asset('css/guestLookup.css')}}">
@endsection

@section('content')
    <div class="container mt-4">
    <h2 class="shopTitle">비회원 주문조회</h2>
    <div class="centered-div">
        <input type="text" id="orderId" name="orderId" placeholder="주문번호">
        <button type="button" id="lookupBtn" class="btn btn-outline-primary">조회</button>
    </div>
    <div id="result"></div>
</div>
@endsection

@section('js')
<!-- 기본 URL을 JavaScript 변수로 전달 -->
<script>
    window.appUrl = "{{ url('/') }}";
</script>
<script src="{{ asset('js/guestLookup.js') }}"></script>
@endsection