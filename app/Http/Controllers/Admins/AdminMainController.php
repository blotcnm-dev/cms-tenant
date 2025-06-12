<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use App\Models\Statistics\StatisticsJoin;
use App\Models\Statistics\StatisticsVisit;
use DateInterval;
use DatePeriod;
use DateTime;
use Exception;

class AdminMainController extends Controller
{
    public function __construct()
    {

    }


    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        //dd("여기는 인덱스");
        $startDate = now()->subDays(9)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        // table 주문데이터
        $saleData = [];

        // 게시글
//        $boardController = new BoardController();
//        $bbsData = $boardController->dashBoardPostCount();

        // 회원 포인트 / 예치금 합계
        /*$memberTotal = getConfigJson('memberTotal');

        // 공지사항
        $request = new Request();
        $request->merge(['limit' => 6]);
        $notice = (new BoardController())->index($request, 'notice')['data']['rows'];

        // 1:1 문의
        $request = new Request();
        $request->merge(['limit' => 6]);
        $inquiry = (new BoardController())->index($request, 'inquiry')['data']['rows'];

        // 상품문의
        $request = new Request();
        $request->merge(['limit' => 6]);
        $productInquiry = (new BoardController())->index($request, 'contact_all')['data']['rows'];*/


        return view('admin.main', [
//            'startDate' => $startDate,
//            'endDate' => $endDate,
//            'weekStartDate' => now()->subDays(6)->format('Y-m-d'),
//            'weekEndDate' => now()->format('Y-m-d'),
//            'topData' => [
//                'join' => $this->join(),
//            ],
//            'saleData' => $saleData,
//            'bbsData' => $bbsData['data'],
//            'point' => $memberTotal->point ?? 0,
//            'deposit' => $memberTotal->deposit ?? 0,
            # 'notices' => $notice,
            # 'inquirys' => $inquiry,
            # 'productInquirys' => $productInquiry
        ]);
    }


    /**
     * @return array
     */
    public function btmStat(): array
    {
        try
        {
            $saleData = $joinData = $visitData = [];

            $startDate = now()->subDays(9)->format('Y-m-d');
            $endDate = now()->format('Y-m-d');

            $days = $this->getDatesStartToLast($startDate, $endDate);

            $statisticsJoinModel = new StatisticsJoin();
            $statisticsVisitModel = new StatisticsVisit();
            foreach (range(explode('-', $startDate)[0], explode('-', $endDate)[0]) as $y) {
                $res = $statisticsJoinModel->getData($y, $startDate, $endDate, 'day');
                foreach ($res as $data) {
                    $joinData[$y.'-'.$data->kind] = $data->device_pc + $data->device_mobile_web + $data->device_mobile_app;
                }

                $_startDate = $y.'-01-01 00:00:00';
                $_endDate = $y.'-12-31 23:59:59';
                $res = $statisticsVisitModel->getSubData($y, $_startDate, $_endDate, 'day');
                foreach ($res as $data) {
                    $visitData[$y.'-'.$data->kind] = $data->total_pc + $data->total_mobile;
                }
            }

            $res = $statisticsVisitModel->getSubTodayData(now()->format('Y-m-d 00:00:00'), now()->format('Y-m-d 23:59:59'), 'day');
            foreach ($res as $data) {
                $visitData[now()->format('Y').'-'.$data->day] = $data->cnt;
            }
            //$joinData = (new StatisticsJoin())->getData(2023, $startDt, $endDt, 'day');
            //$visitTodayData = (new StatisticsVisit())->getSubTodayData($startDt, $endDt, 'day');
            //$visitData = (new StatisticsVisit())->getMainTodayData($startDt, $endDt, 'day');

            $orderController = new OrderController();
            $saleDataRes = $orderController->dashBoardOrderChartData(['start_date' => $startDate, 'end_date' => $endDate]);
            foreach ($saleDataRes as $data) {
                $saleData[$data->cdate] = round((int)$data->total_paid_price);
            }

            $sales = $join = $visit = [];
            foreach ($days as $day) {
                $sales[] = [$day, $saleData[$day] ?? 0];
                $join[] = [$day, $joinData[$day] ?? 0];
                $visit[] = [$day, ($visitData[$day] ?? 0) + ($visitTodayData[$day] ?? 0)];
            }

            return [
                'success' => true,
                'data' => [
                    'sales' => $sales,
                    'visit' => $visit,
                    'join' => $join
                ]
            ];
        }
        catch (Exception $e)
        {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    /**
     * 상단 가입 차트 데이터
     * @return array
     */
    protected function join(): array
    {
        $data = ['today' => 0, 'week' => 0, 'month' => 0];

        // 일 통계
        $today = now()->format('Y-m-d');
        [$y, $m, $d] = explode('-', $today);
        $res = (new StatisticsJoin())->getData($y, $today, $today, 'day');

        $data['today'] = ($res[0]->device_pc ?? 0) + ($res[0]->device_mobile_web ?? 0) + ($res[0]->device_mobile_app ?? 0) + ($res[0]->device_tablet ?? 0);

        // 주 통계
        $startDate = now()->startOfWeek()->format('Y-m-d');
        $endDate = now()->endOfWeek()->format('Y-m-d');
        [$y, $m, $d] = explode('-', $startDate);
        $res = (new StatisticsJoin())->getData($y, $startDate, $endDate, 'day');
        foreach($res as $item) {
            $data['week'] += (int)($item->device_pc ?? 0);
            $data['week'] += (int)($item->device_mobile_web ?? 0 + $item->device_mobile_app ?? 0);
            $data['week'] += (int)($item->device_tablet ?? 0);
        }

        // 월 통계
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        [$y, $m, $d] = explode('-', $startDate);
        $res = (new StatisticsJoin())->getData($y, $startDate, $endDate, 'day');
        foreach($res as $item) {
            $data['month'] += (int)($item->device_pc ?? 0);
            $data['month'] += (int)($item->device_mobile_web ?? 0 + $item->device_mobile_app ?? 0);
            $data['month'] += (int)($item->device_tablet ?? 0);
        }

        return $data;
    }


    /**
     * @param $startDate
     * @param $lastDate
     * @return array|string
     */
    public function getDatesStartToLast($startDate, $lastDate): array|string
    {
        $period = $dates = [];
        $regex = '/^\d{4}-\d{2}-\d{2}$/';
        if (!(preg_match($regex, $startDate) && preg_match($regex, $lastDate))) {
            return "Not Date Format";
        }

        try {
            $period = new DatePeriod(new DateTime($startDate), new DateInterval('P1D'), new DateTime($lastDate . " +1 day"));
        } catch (Exception $e) {
            return "Error";
        }

        foreach ($period as $date) {
            $dates[] = $date->format("Y-m-d");
        }

        return $dates;
    }
}
