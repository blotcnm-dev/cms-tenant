<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ClientSession
{
    /**
     * Handle an incoming request.
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->header('client-session-id')) { //
            $clientSessionId = $request->header('client-session-id');
            $sessions = [];

            $sessionModel = new \App\Models\CmcoSession();
            $session = $sessionModel->select('*')->where('session_id', $clientSessionId)->first();

            if (!$this->checkUrl($request->header('referrer') ?: null)) {
                $sessions['referrer'] = $request->header('referrer');
            }

            if ($session) { // 유지중
                if (count($sessions) > 0) {
                    setClientSession($sessions);
                } else {
                    // 기본 업데이트
                    $sessionModel->setUpdate(['updated_at' => now()], ['session_id' => $clientSessionId]);
                }
            } else { // 신규 접속
                // 접속 url 를 기록한다.
                if ($request->header('client-access-url') !== null) {
                    $sessions['client_access_url'] = $request->header('client-access-url');
                }

                // 세션을 기록한다.
                if(setClientSession($sessions)) {
                    // 접속 로그 기록
                    $this->setAccessLog($request);
                }
            }
        }

        return $next($request);
    }

    /**
     * 첫 접근 로그
     * @param Request $request
     * @return void
     */
    private function setAccessLog(Request $request): void
    {
        try {
            $agent = new \Jenssegers\Agent\Agent();
            $agent->setHttpHeaders($request->headers);

            (new \App\Models\Statistics\StatisticsVisit())->setInsert([
                'session_id' => $request->header('client-session-id'),
                'ip' => "DB::raw|INET_ATON('" . getRealIP() . "')",
                'device' => $agent->isMobile() ? 'MO' : 'PC',
                'referrer' => $request->header('referrer'),
                'agent' => $request->header('user-agent'),
                'is_revisit' => $request->header('revisit') === 'true' ? 1 : 0,
                'week' => now()->format('w'),
                'created_at' => now()
            ]);
        }
        catch (\Exception $e) {
            echo $e->getMessage();
        }
    }


    /**n
     * @param $domain
     * @return bool
     */
    private function checkUrl($domain): bool
    {
        if (is_null($domain)) {
            return true;
        }

        $domain = Str::replaceFirst('https://', '', $domain);
        $domain = Str::replaceFirst('http://', '', $domain);
        $domain = Str::endsWith($domain, '/') ? $domain : "{$domain}/";

        $shopDefaultConfig = getAppGlobalData('shopDefaultConfig');
        $shopDomain = $shopDefaultConfig->shop_domain ?: null;

        if (is_null($shopDomain)) {
            return true;
        }

        $shopDomain = Str::replaceFirst('https://', '', $shopDomain);
        $shopDomain = Str::replaceFirst('http://', '', $shopDomain);

        return Str::is(Collection::make([$shopDomain])->map(function ($uri) {
            return trim($uri).'/*';
        })->all(), $domain);
    }
}
