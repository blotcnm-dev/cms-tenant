<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;

class CheckAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 요청된 경로가 '/master'으로 시작하는지 확인합니다.
        if ($request->is('master') || $request->is('master/*')) {
            // 관리자 페이지 접속 시 세션에 'is_admin'을 true로 설정합니다.
            Session::put('is_admin', true);
        } else {
            // 관리자 페이지가 아닌 경우 세션에서 'is_admin'을 제거합니다.
            Session::forget('is_admin');
        }

        return $next($request);
    }
}
