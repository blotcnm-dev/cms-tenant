<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanctumUserAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $type
     * @param string $key
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     */
    public function handle(Request $request, Closure $next, string $type, string $key = ''): mixed
    {
        if (!empty($type)) {
            switch ($type) {
                case 'admin':
                    if (!isApiAdmin()) {
                        return response('Unauthorized', 401);
                    }
                    break;

                case 'user':
                    if (!isApiAdmin()) {
                        if (empty(auth()->user()->member_id) || empty($request->{$key})) {
                            abort(401, '인증오류');
                        }

                        $member_id = is_array($request->{$key}) ? (int)$request->{$key}[0] : (int)$request->{$key};
                        if (auth()->user()->member_id !== $member_id) {
                            abort(401, '인증오류');
                        }
                    }
                    break;
            }
        }

        return $next($request);
    }
}
