<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// 매일 자정에 장바구니에서 수정된지 2년 넘은 상품 자동 삭제
Schedule::command('carts:deleteOldItems')->daily();