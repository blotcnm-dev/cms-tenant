<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FrontendDataService
{
    private $jsonFileName = 'site-set-data.json';
    private $storagePath = 'frontend';

    /**
     * 프론트엔드 데이터를 JSON 파일로 생성
     */
    public function generateFrontendDataFile()
    {
        try {
            $frontendData = $this->collectFrontendData();

            // 디렉토리 생성 확인
            $storageDirectory = storage_path('app/' . $this->storagePath);
            if (!is_dir($storageDirectory)) {
                mkdir($storageDirectory, 0755, true);
            }


            // Storage 방식으로 저장
            $relativePath = $this->storagePath . '/' . $this->jsonFileName;
            Storage::put($relativePath, json_encode($frontendData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            Log::info('Frontend data file generated successfully');
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to generate frontend data file: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * 프론트엔드에 필요한 데이터 수집
     */
    private function collectFrontendData()
    {
        return [
            'menus' => $this->getMenuData(),
            'settings' => $this->getSiteSettings(),
            'generated_at' => now()->toISOString(),
            'version' => time() // 캐시 무효화용
        ];
    }

    /**
     * 메뉴 데이터 조회
     */
    private function getMenuData()
    {
        $allMenus = DB::table('bl_menus')
            ->where('is_active', '1')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->get()
            ->keyBy('id');

        $jsonMenus = json_decode($allMenus, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return $jsonMenus;
    }

    /**
     * 사이트 설정 데이터 조회
     */
    private function getSiteSettings()
    {
        // 사이트 설정 내용
        $site_config = DB::table('bl_config')
            ->where('code_group', 'site')
            ->orderBy('code', 'asc')
            ->get();



        // 푸터, SNS, 로그인 설정 데이터 가져오기
        $footerSettings = $site_config->where('code', 'footer_settings')->first();
        $snsSettings = $site_config->where('code', 'sns_settings')->first();
        $loginSettings = $site_config->where('code', 'login_settings')->first();
        // JSON 데이터 디코딩
        $footerData = $footerSettings ? json_decode($footerSettings->value, true) : [];
        $activeFooterData = array_filter($footerData, function($item) {
            return $item['active'] == 1;
        });
        usort($activeFooterData, function($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        //$snsData = $snsSettings ? json_decode($snsSettings->value, true) : [];


        $snsData = $snsSettings ? json_decode($snsSettings->value, true) : [];
        $activeSnsData = array_filter($snsData, function($item) {
            return $item['active'] == 1;
        });
        usort($activeSnsData, function($a, $b) {
            return $a['order'] <=> $b['order'];
        });


        $loginData = $loginSettings ? json_decode($loginSettings->value, true) : [];


        $metaData = [
            'meta-title' =>  $site_config->where('code', 'meta-title')->first(),
            'meta-desc' =>  $site_config->where('code', 'meta-desc')->first(),
            'meta-keyword' =>  $site_config->where('code', 'meta-keyword')->first(),
            'meta-author' =>  $site_config->where('code', 'meta-author')->first(),
            'meta-favicon' =>  $site_config->where('code', 'favicon')->first(),
            'meta-home-name-kr' =>  $site_config->where('code', 'home_name_kr')->first(),
            'meta-home-name-en' =>  $site_config->where('code', 'home_name_en')->first(),
        ];

        $scriptData = [
            'gtm-head' =>  $site_config->where('code', 'gtm-head')->first(),
            'gtm-body' =>  $site_config->where('code', 'gtm-body')->first(),
            'gta-head' =>  $site_config->where('code', 'gta-head')->first(),
        ];


        $combinedData = [
            'footer' => $activeFooterData,
            'sns' => $activeSnsData,
            'login' => $loginData,
            'meta' => $metaData,
            'script' => $scriptData,
        ];

        return $combinedData;
    }

    /**
     * JSON 파일에서 데이터 읽기
     */
    public function getFrontendData()
    {
        $storagePath = storage_path('app/' . $this->storagePath . '/' . $this->jsonFileName);
        if (file_exists($storagePath)) {
            $content = file_get_contents($storagePath);
            return json_decode($content, true);
        }

        // JSON 파일이 없으면 새로 생성
        $this->generateFrontendDataFile();
        return $this->collectFrontendData();
    }

    /**
     * JSON 파일 존재 여부 확인
     */
    public function jsonFileExists()
    {
        return Storage::exists('app/'. $this->storagePath. '/' . $this->jsonFileName);
    }

    /**
     * JSON 파일 삭제
     */
    public function clearFrontendDataFile()
    {
        Storage::delete('app/'. $this->storagePath. '/' . $this->jsonFileName);
    }
}
