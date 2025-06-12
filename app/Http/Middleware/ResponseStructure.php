<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ResponseStructure
{
    /**
     * 결과에 데이터를 추가 한다.
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if (getClientSessionKey()) {

            // 데이터 로드
            $data = $response->getOriginalContent();

            if (isset($data['success']) && $data['success']) {
                // headers
                if (($request->header('client-include-headers') !== null) && isset($data['data'])) {
                    // 기본 해더 생성
                    $headers = (new \App\Libraries\HeaderLibrary())->getHeaders($request);

                    if (is_array($data['data']) && !isset($data['data']['headers'])) {
                        $data['data']['headers'] = $headers;
                    } else if (!isset($data['data']->headers)) {
                        $data['data']->headers = $headers;
                    }
                }

                // analytics
                if (($request->header('client-include-analytics') !== null) && isset($data['data'])) {
                    // 기본 해더 생성
                    $analytics = [];
                    $analytics['ga'] = getConfigJson('ga');
                    $analytics['ifdo'] = getConfigJson('ifdo');

                    if (is_array($data['data']) && !isset($data['data']['analytics'])) {
                        $data['data']['analytics'] = $analytics;
                    } else if (!isset($data['data']->analytics)) {
                        $data['data']->analytics = $analytics;
                    }
                }

                // footers
                if (($request->header('client-include-footers') !== null) && isset($data['data'])) {
                    // 기본 풋터 생성
                    $footers = (new \App\Libraries\HeaderLibrary())->getFooters($request);

                    if (is_array($data['data']) && !isset($data['data']['footers'])) {
                        $data['data']['footers'] = $footers;
                    } else if (!isset($data['data']->headers)) {
                        $data['data']->footers = $footers;
                    }
                }

                // pages
                if (($request->header('client-include-pages') !== null) && isset($data['data'])) {
                    // 기본 페이지내 생성
                    $pages = (new \App\Libraries\HeaderLibrary())->getPages($request);

                    if (is_array($data['data']) && !isset($data['data']['common_pages'])) {
                        $data['data']['common_pages'] = $pages;
                    } else if (!isset($data['data']->common_pages)) {
                        $data['data']->common_pages = $pages;
                    }
                }

                // 오리지널 세팅
                $response->original = $data;

                try {

                    // 실 데이터 셋
                    $response->setContent(json_encode($data, JSON_THROW_ON_ERROR));

                } catch (\JsonException $e) {

                }
            }
        }

        return $response;
    }
}
