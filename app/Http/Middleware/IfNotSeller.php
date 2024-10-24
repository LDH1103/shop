<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IfNotSeller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            $error = '로그인 후 이용가능한 페이지 입니다.';

            // 요청한 URL을 세션에 저장
            session()->put('url.intended', $request->url());

            return redirect()->route('users.login')->with('alert', $error);
        }

        if (Auth::user()->admin_flg !== '1') {
            $error = '판매자만 접근하실 수 있습니다.';
            return redirect()->route('main')->with('alert', $error);
        }

        return $next($request);
    }
}
