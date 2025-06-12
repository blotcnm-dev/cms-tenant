<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use Carbon\Carbon;

class PromotionController extends Controller
{
    // 캐시 설정
    protected $cachePrefix = 'banners';
    protected $cacheTtl = 600; // 10분

    /**
     * 메인 슬라이더 배너 (position 1-5) - 캐시 적용
     */
    public function getBanners(int $position, $device = 'pc')
    {
//        // 캐시 키 생성
//        $cacheKey = "{$this->cachePrefix}_pos_{$position}_{$device}";
//
//        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($position, $device) {
//            return $this->fetchBannersFromDB($position, $device);
//        });

        $query = DB::table('bl_promotions')
            ->where('promotions_type', 'banner')
            ->where('is_state', 'Y')
            ->where('position', $position)
            ->where(function ($query) {
                $query->where('is_view', 'always')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('is_view', 'period')
                            ->whereDate('sdate', '<=', Carbon::today())
                            ->whereDate('edate', '>=', Carbon::today());
                    });
            })
            ->orderBy('created_at', 'desc');

        // 디바이스별 쿼리 실행
        if ($device === 'mobile') {
            $banners = $query->select([
                'promotions_id',
                'title',
                DB::raw('COALESCE(mo_img, pc_img) as image_url'),
                'path',
                'target',
                'info',
                'position'
            ])->get();
        } else {
            $banners = $query->select([
                'promotions_id',
                'title',
                'pc_img as image_url',
                'path',
                'target',
                'info',
                'position'
            ])->get();
        }
        //dd($query->toSql(), $query->getBindings());

        return $banners->map(function ($banner) {
            return [
                'id' => $banner->promotions_id,
                'title' => $banner->title,
                'image' => $banner->image_url,
                'link' => $banner->path,
                'target' => $banner->target ?: '_self',
                'description' => $banner->info,
                'position' => $banner->position
            ];
        });



    }

    /**
     * DB에서 배너 데이터 가져오기 (실제 쿼리 실행)
     */
    private function fetchBannersFromDB(int $position, $device = 'pc')
    {
        $query = DB::table('bl_promotions')
            ->where('promotions_type', 'banner')
            ->where('is_state', 'Y')
            ->where('position', $position)
            ->where(function ($query) {
                $query->where('is_view', 'always')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('is_view', 'period')
                            ->whereDate('sdate', '<=', Carbon::today())
                            ->whereDate('edate', '>=', Carbon::today());
                    });
            })
            ->orderBy('created_at', 'desc');

        // 디바이스별 쿼리 실행
        if ($device === 'mobile') {
            $banners = $query->select([
                'promotions_id',
                'title',
                DB::raw('COALESCE(mo_img, pc_img) as image_url'),
                'path',
                'target',
                'info',
                'position'
            ])->get();
        } else {
            $banners = $query->select([
                'promotions_id',
                'title',
                'pc_img as image_url',
                'path',
                'target',
                'info',
                'position'
            ])->get();
        }
        //dd($query->toSql(), $query->getBindings());

        return $banners->map(function ($banner) {
            return [
                'id' => $banner->promotions_id,
                'title' => $banner->title,
                'image' => $banner->image_url,
                'link' => $banner->path,
                'target' => $banner->target ?: '_self',
                'description' => $banner->info,
                'position' => $banner->position
            ];
        });
    }
    /**
     * 현재 노출되어야 할 배너들을 가져오는 메인 함수s
     */
    public function getActiveBanners($device = 'all', $type = 'banner')
    {
        $query = DB::table('bl_promotions')
            ->where('promotions_type', $type)
            ->where('is_state', 'Y')
            ->where(function ($q) {
                $q->where('is_view', 'always')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('is_view', 'period')
                            ->whereDate('sdate', '<=', Carbon::today())
                            ->whereDate('edate', '>=', Carbon::today());
                    });
            });

        // 디바이스 필터링
        if ($device !== 'all') {
            $deviceFilter = ['A']; // 전체

            if ($device === 'pc') {
                $deviceFilter[] = 'P';
            } elseif ($device === 'mobile') {
                $deviceFilter[] = 'M';
            }

            $query->whereIn('device', $deviceFilter);
        }

        return $query->orderBy('position')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * 메인페이지용 배너 데이터
     */
    public function getMainPageBanners(Request $request)
    {
        $device = $request->get('device', 'pc'); // pc, mobile

        $banners = [
            'main' => $this->getMainBanners($device),
            'sidebar' => $this->getSidebarBanners($device),
            'bottom' => $this->getBottomBanners($device),
            'popup' => $this->getPopupBanners($device),
            'floating' => $this->getFloatingBanners($device),
            'top_fixed' => $this->getTopFixedBanners($device)
        ];

        if ($request->ajax()) {
            return response()->json($banners);
        }

        return view('main.index', compact('banners'));
    }




    /**
     * 메인 슬라이더 배너 (position 1-5)
     */
    public function getMainBanners($device = 'pc')
    {
        $imageColumn = $device === 'mobile'
            ? DB::raw('COALESCE(mo_img, pc_img) as image_url')
            : 'pc_img as image_url';

        return DB::table('bl_promotions')
            ->select([
                'promotions_id',
                'title',
                $imageColumn,
                'path',
                'target',
                'info',
                'position'
            ])
            ->where('promotions_type', 'banner')
            ->where('is_state', 'Y')
            ->whereBetween('position', [1, 5])
            ->whereIn('device', $this->getDeviceFilter($device))
            ->where(function ($query) {
                $query->where('is_view', 'always')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('is_view', 'period')
                            ->whereDate('sdate', '<=', Carbon::today())
                            ->whereDate('edate', '>=', Carbon::today());
                    });
            })
            ->orderBy('position')
            ->get()
            ->map(function ($banner) {
                return [
                    'id' => $banner->promotions_id,
                    'title' => $banner->title,
                    'image' => $banner->image_url,
                    'link' => $banner->path,
                    'target' => $banner->target ?: '_self',
                    'description' => $banner->info,
                    'position' => $banner->position
                ];
            });
    }

    /**
     * 사이드바 배너 (position 6-10)
     */
    public function getSidebarBanners($device = 'pc')
    {
        $imageColumn = $device === 'mobile'
            ? DB::raw('COALESCE(mo_img, pc_img) as image_url')
            : 'pc_img as image_url';

        return DB::table('bl_promotions')
            ->select([
                'promotions_id',
                'title',
                $imageColumn,
                'path',
                'target',
                'info'
            ])
            ->where('promotions_type', 'banner')
            ->where('is_state', 'Y')
            ->whereBetween('position', [6, 10])
            ->whereIn('device', $this->getDeviceFilter($device))
            ->where(function ($query) {
                $query->where('is_view', 'always')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('is_view', 'period')
                            ->whereDate('sdate', '<=', Carbon::today())
                            ->whereDate('edate', '>=', Carbon::today());
                    });
            })
            ->orderBy('position')
            ->get()
            ->map(function ($banner) {
                return [
                    'id' => $banner->promotions_id,
                    'title' => $banner->title,
                    'image' => $banner->image_url,
                    'link' => $banner->path,
                    'target' => $banner->target ?: '_self',
                    'description' => $banner->info
                ];
            });
    }

    /**
     * 하단 배너 (position 11-20)
     */
    public function getBottomBanners($device = 'pc')
    {
        $imageColumn = $device === 'mobile'
            ? DB::raw('COALESCE(mo_img, pc_img) as image_url')
            : 'pc_img as image_url';

        return DB::table('bl_promotions')
            ->select([
                'promotions_id',
                'title',
                $imageColumn,
                'path',
                'target',
                'info'
            ])
            ->where('promotions_type', 'banner')
            ->where('is_state', 'Y')
            ->whereBetween('position', [11, 20])
            ->whereIn('device', $this->getDeviceFilter($device))
            ->where(function ($query) {
                $query->where('is_view', 'always')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('is_view', 'period')
                            ->whereDate('sdate', '<=', Carbon::today())
                            ->whereDate('edate', '>=', Carbon::today());
                    });
            })
            ->orderBy('position')
            ->get()
            ->map(function ($banner) {
                return [
                    'id' => $banner->promotions_id,
                    'title' => $banner->title,
                    'image' => $banner->image_url,
                    'link' => $banner->path,
                    'target' => $banner->target ?: '_self',
                    'description' => $banner->info
                ];
            });
    }

    /**
     * 팝업 배너
     */
    public function getPopupBanners($device = 'pc')
    {
        $imageColumn = $device === 'mobile'
            ? DB::raw('COALESCE(mo_img, pc_img) as image_url')
            : 'pc_img as image_url';

        return DB::table('bl_promotions')
            ->select([
                'promotions_id',
                'title',
                $imageColumn,
                'path',
                'target',
                'info',
                'is_today'
            ])
            ->where('promotions_type', 'popup')
            ->where('is_state', 'Y')
            ->where(function ($query) {
                $query->where('is_view', 'always')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('is_view', 'period')
                            ->whereDate('sdate', '<=', Carbon::today())
                            ->whereDate('edate', '>=', Carbon::today());
                    });
            })
            ->get()
            ->map(function ($banner) {
                return [
                    'id' => $banner->promotions_id,
                    'title' => $banner->title,
                    'image' => $banner->image_url,
                    'link' => $banner->path,
                    'target' => $banner->target ?: '_self',
                    'description' => $banner->info,
                    'is_today'=> $banner->is_today
                ];
            });
    }

    /**
     * 플로팅 배너 (position 21-25)
     */
    public function getFloatingBanners($device = 'pc')
    {
        $imageColumn = $device === 'mobile'
            ? DB::raw('COALESCE(mo_img, pc_img) as image_url')
            : 'pc_img as image_url';

        $floating = DB::table('bl_promotions')
            ->select([
                'promotions_id',
                'title',
                $imageColumn,
                'path',
                'target',
                'info'
            ])
            ->where('promotions_type', 'banner')
            ->where('is_state', 'Y')
            ->whereBetween('position', [21, 25])
            ->whereIn('device', $this->getDeviceFilter($device))
            ->where(function ($query) {
                $query->where('is_view', 'always')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('is_view', 'period')
                            ->whereDate('sdate', '<=', Carbon::today())
                            ->whereDate('edate', '>=', Carbon::today());
                    });
            })
            ->orderBy('position')
            ->first();

        if ($floating) {
            return [
                'id' => $floating->promotions_id,
                'title' => $floating->title,
                'image' => $floating->image_url,
                'link' => $floating->path,
                'target' => $floating->target ?: '_self',
                'description' => $floating->info
            ];
        }

        return null;
    }

    /**
     * 상단 고정 배너 (position 26-30)
     */
    public function getTopFixedBanners($device = 'pc')
    {
        $topFixed = DB::table('bl_promotions')
            ->select([
                'promotions_id',
                'title',
                'info',
                'path',
                'target'
            ])
            ->where('promotions_type', 'banner')
            ->where('is_state', 'Y')
            ->whereBetween('position', [26, 30])
            ->whereIn('device', $this->getDeviceFilter($device))
            ->where(function ($query) {
                $query->where('is_view', 'always')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('is_view', 'period')
                            ->whereDate('sdate', '<=', Carbon::today())
                            ->whereDate('edate', '>=', Carbon::today());
                    });
            })
            ->orderBy('position')
            ->first();

        if ($topFixed) {
            return [
                'id' => $topFixed->promotions_id,
                'title' => $topFixed->title,
                'text' => $topFixed->info,
                'link' => $topFixed->path,
                'target' => $topFixed->target ?: '_self'
            ];
        }

        return null;
    }

    /**
     * 디바이스 필터 배열 반환
     */
    private function getDeviceFilter($device)
    {
        $filter = ['A']; // 전체

        if ($device === 'pc') {
            $filter[] = 'P';
        } elseif ($device === 'mobile') {
            $filter[] = 'M';
        }

        return $filter;
    }

    /**
     * 배너 클릭 통계 저장
     */
    public function trackBannerClick(Request $request)
    {
        $bannerId = $request->get('banner_id');
        $userIp = $request->ip();

        // 클릭 통계 테이블에 저장 (선택사항)
        DB::table('banner_clicks')->insert([
            'promotions_id' => $bannerId,
            'ip_address' => $userIp,
            'user_agent' => $request->userAgent(),
            'clicked_at' => Carbon::now(),
            'created_at' => Carbon::now()
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * 관리자용 배너 상태 확인
     */
    public function getBannerStatus()
    {
        return DB::table('bl_promotions')
            ->select([
                'promotions_id',
                'title',
                'is_state',
                'is_view',
                'sdate',
                'edate',
                'position',
                DB::raw("
                    CASE
                        WHEN is_state = 'N' THEN '사용안함'
                        WHEN is_view = 'always' THEN '상시노출'
                        WHEN is_view = 'period' AND CURDATE() BETWEEN sdate AND edate THEN '기간노출중'
                        WHEN is_view = 'period' AND CURDATE() < sdate THEN '노출예정'
                        WHEN is_view = 'period' AND CURDATE() > edate THEN '노출종료'
                        ELSE '확인필요'
                    END as status
                "),
                'created_at'
            ])
            ->where('promotions_type', 'banner')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
