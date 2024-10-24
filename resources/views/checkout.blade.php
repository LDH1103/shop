@extends('layouts.app')

@section('title', '결제')

@section('css')
{{-- <link rel="stylesheet" href="{{asset('css/.css')}}"> --}}
@endsection

@section('content')
<div class="container mt-4">
    <h2 class="shopTitle">결제 페이지</h2>
    @foreach($items as $item)
    <li>
        상품 ID: {{ $item['id'] }}, 수량: {{ $item['quantity'] }}
    </li>
@endforeach
</div>
@endsection

@section('js')
{{-- <script src="{{ asset('js/.js') }}"></script> --}}
@endsection