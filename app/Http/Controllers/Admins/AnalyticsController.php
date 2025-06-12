<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    private $client;
    private $propertyId;

    public function __construct()
    {
        try {
            $this->propertyId = '490947108';
            if (!$this->propertyId) {
                throw new Exception('GA4_PROPERTY_ID not found in environment variables');
            }

            $credentialsPath = storage_path('app/analytics/blotcms-ga4-v1.json');
            if (!file_exists($credentialsPath)) {
                throw new Exception('Google Analytics credentials file not found: ' . $credentialsPath);
            }

            $credentialsContent = file_get_contents($credentialsPath);
            $credentials = json_decode($credentialsContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON in credentials file: ' . json_last_error_msg());
            }

            $requiredKeys = ['type', 'project_id', 'private_key', 'client_email'];
            foreach ($requiredKeys as $key) {
                if (!isset($credentials[$key]) || empty($credentials[$key])) {
                    throw new Exception("Missing or empty required key '{$key}' in credentials file");
                }
            }

            $this->client = new BetaAnalyticsDataClient([
                'credentials' => $credentials,
                'projectId' => $credentials['project_id']
            ]);

            \Log::info('Google Analytics client initialized successfully');

        } catch (Exception $e) {
            \Log::error('Analytics Controller Constructor Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function index()
    {
        return view('admin.main');
    }

    /**
     * 대시보드 데이터 API (캐시 처리 적용)
     */
    public function getDashboardData(Request $request)
    {
        try {
            $days = $request->get('days');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            if ($startDate && $endDate) {
                $start = \DateTime::createFromFormat('Y-m-d', $startDate);
                $end = \DateTime::createFromFormat('Y-m-d', $endDate);

                if (!$start || !$end) {
                    throw new \Exception('Invalid date format. Use Y-m-d format.');
                }

                if ($start > $end) {
                    throw new \Exception('Start date cannot be after end date.');
                }

                $today = new \DateTime();
                if ($end > $today) {
                    $end = $today;
                    $endDate = $end->format('Y-m-d');
                }

                $dateRange = ['start_date' => $startDate, 'end_date' => $endDate];
                $daysDiff = $start->diff($end)->days + 1;
                $periodLabel = "{$startDate} ~ {$endDate} ({$daysDiff}일)";

            } else {
                $days = $days ?: 7;
                $endDate = Carbon::today()->format('Y-m-d');
                $startDate = Carbon::today()->subDays($days - 1)->format('Y-m-d');

                $dateRange = ['start_date' => $startDate, 'end_date' => $endDate];
                $periodLabel = $days . ' days';
            }

            // 캐시 키 생성
            $cacheKey = $this->generateCacheKey($startDate, $endDate);

            // 캐시에서 데이터 조회
            $cachedData = $this->getCachedData($cacheKey);

            if ($cachedData) {
                \Log::info("Using cached data for period: {$startDate} to {$endDate}");
                $data = [
                    'success' => true,
                    'data' => array_merge($cachedData, ['period' => $periodLabel]),
                    'cached' => true
                ];
            } else {
                \Log::info("No cache found, fetching fresh data from GA4 API");
                $freshData = $this->fetchAllData($dateRange);

                // 새로운 데이터를 캐시에 저장
                $this->setCachedData($cacheKey, $freshData);

                $data = [
                    'success' => true,
                    'data' => array_merge($freshData, ['period' => $periodLabel]),
                    'cached' => false
                ];
            }

            return response()->json($data);

        } catch (\Exception $e) {
            \Log::error('Analytics dashboard data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 실시간 데이터 API (캐시 처리 안함)
     */
    public function getRealtimeData()
    {
        try {
            $data = [
                'success' => true,
                'data' => [
                    'activeUsers' => $this->getRealTimeUsers(),
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ]
            ];

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 캐시 키 생성
     */
    private function generateCacheKey($startDate, $endDate)
    {
        return "analytics_{$this->propertyId}_{$startDate}_{$endDate}";
    }

    /**
     * 캐시에서 데이터 조회
     */
    private function getCachedData($cacheKey)
    {
        try {
            $cache = DB::table('analytics_cache')
                ->where('cache_key', $cacheKey)
                ->first();

            if ($cache) {
                return json_decode($cache->cache_data, true);
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Cache retrieval error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 캐시에 데이터 저장
     */
    private function setCachedData($cacheKey, $data)
    {
        try {
            DB::table('analytics_cache')->updateOrInsert(
                ['cache_key' => $cacheKey],
                [
                    'cache_data' => json_encode($data),
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );

            \Log::info("Data cached with key: {$cacheKey}");
        } catch (\Exception $e) {
            \Log::error('Cache storage error: ' . $e->getMessage());
        }
    }

    /**
     * GA4에서 모든 데이터 가져오기
     */
    private function fetchAllData($dateRange)
    {
        return [
            'metrics' => $this->getBasicMetrics($dateRange),
            'topPages' => $this->getTopPages($dateRange),
            'datePages' => $this->getDatePages($dateRange),
            'browserPages' => $this->getBrowserPages($dateRange),
            'eventName' => $this->getEventPages($dateRange),
            'dailySourceMedium' => $this->getDailySourceMedium($dateRange),
            'dailyCampaign' => $this->getDailyCampaign($dateRange),
            'deviceCategory' => $this->getDeviceCategory($dateRange),
            'screenResolution' => $this->getScreenResolution($dateRange)
        ];
    }

    /**
     * 기본 메트릭 데이터
     */
    private function getBasicMetrics($dateRange)
    {
        try {
            \Log::info("Getting basic metrics for date range: " . json_encode($dateRange));

            $dateRangeObj = new DateRange([
                'start_date' => $dateRange['start_date'],
                'end_date' => $dateRange['end_date']
            ]);

            $request = (new RunReportRequest())
                ->setProperty('properties/' . $this->propertyId)
                ->setDateRanges([$dateRangeObj])
                ->setMetrics([
                    new Metric(['name' => 'activeUsers']),
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'screenPageViews']),
                    new Metric(['name' => 'bounceRate'])
                ]);

            $response = $this->client->runReport($request);

            if ($response->getRows()->count() > 0) {
                $row = $response->getRows()[0];
                $metrics = $row->getMetricValues();

                $result = [
                    'activeUsers' => (int) $metrics[0]->getValue(),
                    'sessions' => (int) $metrics[1]->getValue(),
                    'pageViews' => (int) $metrics[2]->getValue(),
                    'bounceRate' => round((float) $metrics[3]->getValue() * 100, 2)
                ];

                return $result;
            }

            return [
                'activeUsers' => 0,
                'sessions' => 0,
                'pageViews' => 0,
                'bounceRate' => 0
            ];

        } catch (Exception $e) {
            \Log::error('GA4 Basic Metrics Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 상위 페이지 데이터
     */
    private function getTopPages($dateRange, $limit = 5)
    {
        try {
            $dateRangeObj = new DateRange([
                'start_date' => $dateRange['start_date'],
                'end_date' => $dateRange['end_date']
            ]);

            $request = (new RunReportRequest())
                ->setProperty('properties/' . $this->propertyId)
                ->setDateRanges([$dateRangeObj])
                ->setDimensions([
                    new Dimension(['name' => 'pagePath']),
                    new Dimension(['name' => 'pageTitle'])
                ])
                ->setMetrics([
                    new Metric(['name' => 'screenPageViews'])
                ])
                ->setLimit($limit);

            $response = $this->client->runReport($request);

            $data = [];
            foreach ($response->getRows() as $row) {
                $dimensions = $row->getDimensionValues();
                $metrics = $row->getMetricValues();

                $data[] = [
                    'path' => $dimensions[0]->getValue(),
                    'title' => $dimensions[1]->getValue() ?: $dimensions[0]->getValue(),
                    'views' => (int) $metrics[0]->getValue()
                ];
            }

            return $data;

        } catch (Exception $e) {
            \Log::error('GA4 Top Pages Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 날짜별 데이터
     */
    private function getDatePages($dateRange)
    {
        try {
            $request = (new RunReportRequest())
                ->setProperty('properties/' . $this->propertyId)
                ->setDateRanges([
                    new DateRange([
                        'start_date' => $dateRange['start_date'],
                        'end_date' => $dateRange['end_date']
                    ])
                ])
                ->setDimensions([new Dimension(['name' => 'date'])])
                ->setMetrics([
                    new Metric(['name' => 'activeUsers']),
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'screenPageViews'])
                ]);

            $response = $this->client->runReport($request);

            $data = [];
            foreach ($response->getRows() as $row) {
                $dimensions = $row->getDimensionValues();
                $metrics = $row->getMetricValues();

                $data[] = [
                    'date' => $dimensions[0]->getValue(),
                    'activeUsers' => (int) $metrics[0]->getValue(),
                    'sessions' => (int) $metrics[1]->getValue(),
                    'pageViews' => (int) $metrics[2]->getValue()
                ];
            }

            return $data;

        } catch (Exception $e) {
            \Log::error('GA4 Date Pages Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 브라우저별 데이터
     */
    private function getBrowserPages($dateRange, $limit = 10)
    {
        try {
            $request = (new RunReportRequest())
                ->setProperty('properties/' . $this->propertyId)
                ->setDateRanges([
                    new DateRange([
                        'start_date' => $dateRange['start_date'],
                        'end_date' => $dateRange['end_date']
                    ])
                ])
                ->setDimensions([new Dimension(['name' => 'browser'])])
                ->setMetrics([
                    new Metric(['name' => 'activeUsers']),
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'screenPageViews'])
                ])
                ->setLimit($limit);

            $response = $this->client->runReport($request);

            $data = [];
            foreach ($response->getRows() as $row) {
                $dimensions = $row->getDimensionValues();
                $metrics = $row->getMetricValues();

                $data[] = [
                    'browser' => $dimensions[0]->getValue(),
                    'activeUsers' => (int) $metrics[0]->getValue(),
                    'sessions' => (int) $metrics[1]->getValue(),
                    'pageViews' => (int) $metrics[2]->getValue()
                ];
            }

            return $data;

        } catch (Exception $e) {
            \Log::error('GA4 Browser Pages Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 이벤트 데이터
     */
    private function getEventPages($dateRange, $limit = 10)
    {
        try {
            $request = (new RunReportRequest())
                ->setProperty('properties/' . $this->propertyId)
                ->setDateRanges([
                    new DateRange([
                        'start_date' => $dateRange['start_date'],
                        'end_date' => $dateRange['end_date']
                    ])
                ])
                ->setDimensions([new Dimension(['name' => 'eventName'])])
                ->setMetrics([
                    new Metric(['name' => 'activeUsers']),
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'screenPageViews'])
                ])
                ->setLimit($limit);

            $response = $this->client->runReport($request);

            $data = [];
            foreach ($response->getRows() as $row) {
                $dimensions = $row->getDimensionValues();
                $metrics = $row->getMetricValues();

                $data[] = [
                    'eventName' => $dimensions[0]->getValue(),
                    'activeUsers' => (int) $metrics[0]->getValue(),
                    'sessions' => (int) $metrics[1]->getValue(),
                    'pageViews' => (int) $metrics[2]->getValue()
                ];
            }

            return $data;

        } catch (Exception $e) {
            \Log::error('GA4 Event Pages Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 기기 카테고리별 데이터
     */
    private function getDeviceCategory($dateRange)
    {
        try {
            $request = (new RunReportRequest())
                ->setProperty('properties/' . $this->propertyId)
                ->setDateRanges([
                    new DateRange([
                        'start_date' => $dateRange['start_date'],
                        'end_date' => $dateRange['end_date']
                    ])
                ])
                ->setDimensions([new Dimension(['name' => 'deviceCategory'])])
                ->setMetrics([
                    new Metric(['name' => 'activeUsers']),
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'screenPageViews'])
                ]);

            $response = $this->client->runReport($request);

            $data = [];
            foreach ($response->getRows() as $row) {
                $dimensions = $row->getDimensionValues();
                $metrics = $row->getMetricValues();

                $data[] = [
                    'deviceCategory' => $dimensions[0]->getValue(),
                    'activeUsers' => (int) $metrics[0]->getValue(),
                    'sessions' => (int) $metrics[1]->getValue(),
                    'pageViews' => (int) $metrics[2]->getValue()
                ];
            }

            return $data;

        } catch (Exception $e) {
            \Log::error('GA4 Device Category Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 화면 해상도별 데이터
     */
    private function getScreenResolution($dateRange, $limit = 10)
    {
        try {
            $request = (new RunReportRequest())
                ->setProperty('properties/' . $this->propertyId)
                ->setDateRanges([
                    new DateRange([
                        'start_date' => $dateRange['start_date'],
                        'end_date' => $dateRange['end_date']
                    ])
                ])
                ->setDimensions([new Dimension(['name' => 'screenResolution'])])
                ->setMetrics([
                    new Metric(['name' => 'activeUsers']),
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'screenPageViews'])
                ])
                ->setLimit($limit);

            $response = $this->client->runReport($request);

            $data = [];
            foreach ($response->getRows() as $row) {
                $dimensions = $row->getDimensionValues();
                $metrics = $row->getMetricValues();

                $data[] = [
                    'screenResolution' => $dimensions[0]->getValue(),
                    'activeUsers' => (int) $metrics[0]->getValue(),
                    'sessions' => (int) $metrics[1]->getValue(),
                    'pageViews' => (int) $metrics[2]->getValue()
                ];
            }

            return $data;

        } catch (Exception $e) {
            \Log::error('GA4 Screen Resolution Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 실시간 사용자 (캐시 처리 안함)
     */
    private function getRealTimeUsers()
    {
        try {
            $request = (new \Google\Analytics\Data\V1beta\RunRealtimeReportRequest())
                ->setProperty('properties/' . $this->propertyId)
                ->setMetrics([
                    new Metric(['name' => 'activeUsers'])
                ]);

            $response = $this->client->runRealtimeReport($request);

            if ($response->getRows()->count() > 0) {
                $activeUsers = (int) $response->getRows()[0]->getMetricValues()[0]->getValue();
                return $activeUsers;
            }

            return 0;

        } catch (Exception $e) {
            \Log::error('GA4 Realtime Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * 일자별 세션 소스/매체 데이터
     */
    private function getDailySourceMedium($dateRange, $limit = 5)
    {
        try {
            $request = (new RunReportRequest())
                ->setProperty('properties/' . $this->propertyId)
                ->setDateRanges([
                    new DateRange([
                        'start_date' => $dateRange['start_date'],
                        'end_date' => $dateRange['end_date']
                    ])
                ])
                ->setDimensions([
                    new Dimension(['name' => 'date']),
                    new Dimension(['name' => 'sessionSource']),
                    new Dimension(['name' => 'sessionMedium'])
                ])
                ->setMetrics([
                    new Metric(['name' => 'sessions'])
                ]);

            $response = $this->client->runReport($request);

            $dailyData = [];
            $sourceList = [];

            foreach ($response->getRows() as $row) {
                $dimensions = $row->getDimensionValues();
                $metrics = $row->getMetricValues();

                $date = $dimensions[0]->getValue();
                $source = $dimensions[1]->getValue();
                $medium = $dimensions[2]->getValue();
                $sourceMedium = $source . ' / ' . $medium;
                $sessions = (int) $metrics[0]->getValue();

                if (!isset($dailyData[$date])) {
                    $dailyData[$date] = [];
                }

                if (!isset($dailyData[$date][$sourceMedium])) {
                    $dailyData[$date][$sourceMedium] = 0;
                }
                $dailyData[$date][$sourceMedium] += $sessions;

                $sourceList[$sourceMedium] = ($sourceList[$sourceMedium] ?? 0) + $sessions;
            }

            arsort($sourceList);
            $topSources = array_slice(array_keys($sourceList), 0, $limit);

            $result = [];
            foreach ($dailyData as $date => $sources) {
                $dayData = ['date' => $date];
                foreach ($topSources as $sourceMedium) {
                    $dayData[$sourceMedium] = $sources[$sourceMedium] ?? 0;
                }
                $result[] = $dayData;
            }

            usort($result, function($a, $b) {
                return strcmp($a['date'], $b['date']);
            });

            return [
                'data' => $result,
                'topSources' => $topSources
            ];

        } catch (Exception $e) {
            \Log::error('GA4 Daily Source/Medium Error: ' . $e->getMessage());
            return [
                'data' => [],
                'topSources' => []
            ];
        }
    }

    /**
     * 일자별 세션 캠페인 데이터
     */
    private function getDailyCampaign($dateRange, $limit = 5)
    {
        try {
            $request = (new RunReportRequest())
                ->setProperty('properties/' . $this->propertyId)
                ->setDateRanges([
                    new DateRange([
                        'start_date' => $dateRange['start_date'],
                        'end_date' => $dateRange['end_date']
                    ])
                ])
                ->setDimensions([
                    new Dimension(['name' => 'date']),
                    new Dimension(['name' => 'sessionCampaignName'])
                ])
                ->setMetrics([
                    new Metric(['name' => 'sessions'])
                ]);

            $response = $this->client->runReport($request);

            $dailyData = [];
            $campaignList = [];

            foreach ($response->getRows() as $row) {
                $dimensions = $row->getDimensionValues();
                $metrics = $row->getMetricValues();

                $date = $dimensions[0]->getValue();
                $campaign = $dimensions[1]->getValue();
                $sessions = (int) $metrics[0]->getValue();

                if ($campaign === '(not set)' || $campaign === '(none)') {
                    continue;
                }

                if (!isset($dailyData[$date])) {
                    $dailyData[$date] = [];
                }

                if (!isset($dailyData[$date][$campaign])) {
                    $dailyData[$date][$campaign] = 0;
                }
                $dailyData[$date][$campaign] += $sessions;

                $campaignList[$campaign] = ($campaignList[$campaign] ?? 0) + $sessions;
            }

            arsort($campaignList);
            $topCampaigns = array_slice(array_keys($campaignList), 0, $limit);

            $result = [];
            foreach ($dailyData as $date => $campaigns) {
                $dayData = ['date' => $date];
                foreach ($topCampaigns as $campaign) {
                    $dayData[$campaign] = $campaigns[$campaign] ?? 0;
                }
                $result[] = $dayData;
            }

            usort($result, function($a, $b) {
                return strcmp($a['date'], $b['date']);
            });

            return [
                'data' => $result,
                'topCampaigns' => $topCampaigns
            ];

        } catch (Exception $e) {
            \Log::error('GA4 Daily Campaign Error: ' . $e->getMessage());
            return [
                'data' => [],
                'topCampaigns' => []
            ];
        }
    }

    /**
     * 오래된 캐시 정리 (30일 이상)
     */
    public function cleanupCache()
    {
        try {
            $deleted = DB::table('analytics_cache')
                ->where('created_at', '<', now()->subDays(30))
                ->delete();

            \Log::info("Cache cleanup completed, deleted {$deleted} records");

            return response()->json([
                'success' => true,
                'deleted_records' => $deleted
            ]);
        } catch (\Exception $e) {
            \Log::error('Cache cleanup error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
