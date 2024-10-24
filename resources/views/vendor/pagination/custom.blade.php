{{-- ---------------블록 페이징----------------- --}}
@php
    // 블록으로 묶을(한번에 보여줄) 페이지 수
    $block = 10;

    // 현재 페이지를 중심으로 좌측에 표시할 첫 번째 페이지
    // max() : 계산 결과가 1보다 작을 경우 1로 설정하여 음수나 0이 되는 것을 방지
    // floor($block / 2) : $block의 절반 값을 내림하여 현재 페이지를 중심으로 시작 페이지(가장 왼쪽에 표시될 페이지)를 설정
    // ex) $block = 5일떄 floor($block / 2) = 2, 현재페이지가 7페이지일때 7 - 2 = 5, 시작 페이지는 5페이지
    $startPage = (int)max(1, $paginator->currentPage() - floor($block / 2)); // float형식으로 바껴서 ===연산자가 안먹히기때문에 int형으로 다시바꿔줌

    // $startPage를 기준으로 우측에 표시할 마지막 페이지를 계산
    // min(..., $paginator->lastPage()) : 계산 결과가 전체 페이지 수($paginator->lastPage())보다 큰 경우 전체 페이지 수로 설정
    // startPage + $block - 1는 $startPage부터 $block의 개수만큼 페이지를 표시하므로 마지막 페이지(가장 오른쪽에 표시될 페이지)를 설정
    // ex) $startPage = 5, $block = 5일떄 마지막페이지는 9페이지
    $endPage = min($startPage + $block - 1, $paginator->lastPage());
@endphp

{{-- 첫 페이지 버튼 --}}
@if ($paginator->onFirstPage())
    <a href="{{ $paginator->url(1) }}" rel="prev" class="pageHidden"><<</a>
@else
    <a href="{{ $paginator->url(1) }}" rel="prev" class="page-item"><<</a>
@endif

{{-- 이전 블럭 버튼 --}}
@php
    // 최소 1페이지, 현재페이지 - $block
    $previousBlockPage = max(1, $paginator->currentPage() - $block);
@endphp
@if ($paginator->onFirstPage())
    <a href="{{ $paginator->url($previousBlockPage) }}" rel="prev" class="pageHidden"><</a>
@else
    <a href="{{ $paginator->url($previousBlockPage) }}" rel="prev" class="page-item"><</a>
@endif

{{-- 페이징 --}}
{{-- range() : 지정된 범위의 숫자를 생성하여 배열로 반환 --}}
@foreach(range($startPage, $endPage) as $i)
    @if($i === $paginator->currentPage())
        {{-- 현재 페이지면 밑줄 --}}
        <a href="{{$paginator->url($i)}}" style="text-decoration: underline;" class="page-item">{{$i}}</a>
    @else
        <a href="{{$paginator->url($i)}}" class="page-item" class="page-item">{{$i}}</a>
    @endif
@endforeach

{{-- 다음 블럭 버튼 --}}
@php
    // 최대 마지막 페이지, 현재 페이지 + $block
    $nextBlockPage = min($paginator->lastPage(), $paginator->currentPage() + $block);
@endphp
@if ($paginator->hasMorePages())
    <a href="{{ $paginator->url($paginator->lastPage()) }}"><span style="color: black; margin-right:10px;">...</span>{{ $paginator->lastPage() }}</a>
    <a href="{{ $paginator->url($nextBlockPage) }}" rel="next" class="page-item">></a>
@else
    <a href="{{ $paginator->url($nextBlockPage) }}" rel="next" class="pageHidden">></a>
@endif

{{-- 마지막 페이지 --}}
@if ($paginator->hasMorePages())
    <a href="{{ $paginator->url($paginator->lastPage()) }}" rel="next" class="page-item">>></a>
@else
    <a href="{{ $paginator->url($paginator->lastPage()) }}" rel="next" class="pageHidden">>></a>
@endif
