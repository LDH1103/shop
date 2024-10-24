<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class checkLogin
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
        return $next($request);
    }
}
