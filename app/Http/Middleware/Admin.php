<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
//use Barryvdh\Debugbar\Facade as Debugbar;
class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function handle(Request $request, Closure $next)
    {
        // 사이트 접근 차단.
        $securityConfig = getAppGlobalData('securityConfig');


        if (isset($securityConfig->user_reject_ip) && in_array(getRealIP(), $securityConfig->user_reject_ip, true)) {
            abort(404);
        }

        if(!session()->get('blot_adid'))
        {
            var_dump(session()->all());
            var_dump("세션 없슴. ");
            //return redirect()->route('master.login', ['referrer' => $request->url()]);
        }

        return $next($request);
    }
}
