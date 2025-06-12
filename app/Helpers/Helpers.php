<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (!function_exists('removeCommas')) {
    function removeCommas(string $value) : string|null
    {
        return preg_replace("/[^0-9]/", "",$value) ;
    }
}

if (!function_exists('routeChk')) {
    /**
     * 라우트 확인 api or web
     *
     * @param mixed $request
     * @return mixed
     */
    function routeChk(mixed $request) : mixed
    {
        return $request->route()->gatherMiddleware()[0];
    }
}

if (!function_exists('calculateAge')) {
    /**
     * 나이계산
     * @param string|null $birthday
     * @return int
     */
    function calculateAge(string $birthday = null): int
    {
        if (empty($birthday)) {
            return 0;
        }

        $birthDate = Carbon\Carbon::parse($birthday);
        return Carbon\Carbon::now()->diffInYears($birthDate);
    }
}

if (!function_exists('decimalRoundCut')) {

    /**
     * 소수점 자리수 제거
     *
     * @param float $val
     * @param int $num
     * @return float
     */
    function decimalRoundCut(float $val, int $num) : float
    {
        return round($val, $num, PHP_ROUND_HALF_DOWN);
    }
}

if (!function_exists('TrimTrailingZeroes')) {

    /**
     * 소수점 0 제거
     *
     * @param float $val
     * @return float
     */
    function TrimTrailingZeroes($val) {
        if(strpos($val,'.')!==false) $val = rtrim($val,'0');
        return rtrim($val,'.') ?: '0';
    }
}

if (!function_exists('isJson')) {

    /**
     * json 요청 여부 확인
     *
     * @param mixed $request
     * @return bool
     */
    function isJson(mixed $request) : bool
    {
        return ($request->getContentType() !== '' && strtoupper($request->getContentType()) === 'JSON') ? true : false;
    }
}

if (!function_exists('discountRate')) {

    /**
     * 할인율
     *
     * @param int $price
     * @param int $discountPrice
     * @return mixed
     */
    function discountRate(int $price, int $discountPrice) : mixed
    {
        if ($price <= 0) return 0;

        $ratio = round((($price - $discountPrice) / $price) * 100);
        return $ratio;
    }
}

if (!function_exists('setNull')) {
    /**
     * null 체크 및 기본값 세팅
     *
     * @param mixed $obj
     * @param mixed $val
     * @return mixed
     */
    function setNull(mixed $obj, mixed $val) : mixed
    {
        if (isset($obj)) {
            if (gettype($obj) === 'object' || gettype($obj) === 'array') {
                return !empty($obj) ? $obj : (isset($val) ? $val : null);
            } else {
                return $obj !== '' ? $obj : (isset($val) ? $val : '');
            }
        } else {
            if (gettype($obj) === 'object' || gettype($obj) === 'array') {
                return isset($val) ? $val : null;
            } else {
                return isset($val) ? $val : '';
            }
        }
        return 0;
    }
}

if (!function_exists('phoneHyphenAdd')) {
    /**
     * 폰번호 하이픈 추가
     *
     * @param string $str
     * @return string|null
     */
    function phoneHyphenAdd(string $str) : string|null
    {
        //숫자이외 제거
        $tel = preg_replace("/[^0-9]*/s", "", $str);
        $response = $tel;

        if (substr($tel,0,2) =='02')
        {
            $response = preg_replace("/([0-9]{2})([0-9]{3,4})([0-9]{4})$/","\\1-\\2-\\3", $tel);
        }
        else if(substr($tel, 0, 2) =='15' || substr($tel, 0, 2) =='16'||  substr($tel, 0, 2) =='18')
        {
            $response = preg_replace("/([0-9]{4})([0-9]{4})$/", "\\1-\\2", $tel);
        }
        else
        {
            $response = preg_replace("/([0-9]{3})([0-9]{3,4})([0-9]{4})$/", "\\1-\\2-\\3", $tel);
        }

        return $response;
    }
}

if (!function_exists('getConfigJson')) {

    /**
     * 설정 파일 가져오기
     *
     * @param string $configFileName
     * @param string $path
     * @return array|object|null
     */
    function getConfigJson(string $configFileName, string $path = '/common/configs/') : array|object|null
    {
        try
        {
            $path = $path ?: '/common/configs/public/';

            $exists = Illuminate\Support\Facades\Storage::disk('local')->exists($path.$configFileName.'.json');

            if($exists === false) {
                throw new \RuntimeException(__('common.configJsonFileExists.false'));
            }

            return json_decode((string)Illuminate\Support\Facades\Storage::disk('local')->get($path . $configFileName . '.json'), false, 512, JSON_THROW_ON_ERROR);
        }
        catch(Exception $e)
        {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

if(!function_exists('setConfigJson')) {
    /**
     * 설정 파일 기록
     *
     * @param $params
     * @return array|bool[]
     *
       [
            'private_path' => '/config/',    // 변경 가능
            'public_path' => '/public/config',  // 변경
            'file_name' => 'config',
            'backup' => true/false,
            'db' => [
                'write' => true/false
                'group_key' => bl_config.config_group varchar(30)
             ],
            'private_content' => array,   // 내부용
            'public_content' => array     // 외부용
       ];
     */
    function setConfigJson($params) : array
    {
        try
        {
            if (!isset($params['file_name'])) throw new Exception(__('file.filename_empty'));
            if (!isset($params['private_path'])) throw new Exception(__('file.path_empty'));
            if (empty($params['private_path'])) throw new Exception(__('file.path_empty'));
            if (!isset($params['private_content'])) throw new Exception(__('file.content.empty'));
            if (!is_array($params['private_content'])) throw new Exception(__('file.content.format', ['attribute' => '배열']));

            $saveList = [];

            // 내부 관리용
            //Illuminate\Support\Facades\Storage::disk('datas')->makeDirectory($params['private_path']); // 폴더 생성
            $saveList[] = [
                'file' => $params['private_path'] . $params['file_name'] . '.json',
                'content' => $params['private_content']
            ];

            // 외부 공용
            if (!empty($params['public_path'])) {

                if (!isset($params['public_content'])) {
                    throw new \RuntimeException(__('file.content.empty'));
                }
                if (!is_array($params['public_content'])) {
                    throw new \RuntimeException(__('file.content.format', ['attribute' => '배열']));
                }

                //Illuminate\Support\Facades\Storage::disk('datas')->makeDirectory($params['public_path']); // 폴더 생성
                $saveList[] = [
                    'file' => $params['public_path'].$params['file_name'].'.json',
                    'content' => $params['public_content']
                ];
            }

            // 백업 설정
            $backup = false;
            $backup = $params['backup'] ?? $backup;

            $count = count($saveList);
            if ($count > 0) {
                foreach ($saveList as $i => $iValue) {

                    $_file = $iValue['file'];
                    $_content = json_encode($iValue['content'], JSON_THROW_ON_ERROR);

                    // 백업을 요청한 경우
                    if ($backup) {
                        // 기존 파일이 있다면 백업파일로 복사한다.
                        $exists = Illuminate\Support\Facades\Storage::disk('local')->exists($_file);
                        //$exists = Illuminate\Support\Facades\Storage::disk('datas')->exists($_file);
                        if ($exists) {
                            Illuminate\Support\Facades\Storage::disk('local')->copy($_file, $_file.'.backup');
                            //Illuminate\Support\Facades\Storage::disk('datas')->copy($_file, $_file.'.backup');
                        }

                        // 기존 파일 삭제
                        Illuminate\Support\Facades\Storage::disk('local')->delete($_file);
                        //Illuminate\Support\Facades\Storage::disk('datas')->delete($_file);

                        // 신규 파일 생성
                        $res = Illuminate\Support\Facades\Storage::disk('local')->put($_file, $_content);
                        //$res = Illuminate\Support\Facades\Storage::disk('datas')->put($_file, $_content);
                        if (!$res) {
                            throw new \RuntimeException(__('file.complete.fail'));
                        }

                        // 파일이 정상적으로 생성되지 않았다면 이전 파일로 복원한고 실패로 넘긴다.
                        $exists = Illuminate\Support\Facades\Storage::disk('local')->exists($_file);
                        //$exists = Illuminate\Support\Facades\Storage::disk('datas')->exists($_file);
                        if (!$exists) {
                            Illuminate\Support\Facades\Storage::disk('local')->copy($_file.'.backup', $_file);
                            //Illuminate\Support\Facades\Storage::disk('datas')->copy($_file.'.backup', $_file);
                            throw new \RuntimeException(__('file.complete.fail'));
                        }

                    } else {
                        // 무조건 파일 덮어쓰기
                        $res = Illuminate\Support\Facades\Storage::disk('local')->put($_file, $_content);
                        //$res = Illuminate\Support\Facades\Storage::disk('datas')->put($_file, $_content);
                        if (!$res) {
                            throw new \RuntimeException(__('file.complete.fail'));
                        }
                    }
                }
            }

            // DB 기록을 설정한 경우
            // 기록 실패는 따로 처리 하지 않는다.
            // 기록 하겠다고 true 값을 준 경우
            if (isset($params['db']['write'], $params['db']['group_key']) && is_array($params['db']) && $params['db']['write']) {
                $config_model = new App\Models\Config;
                $config_model->setUpdateOrInsert([
                    'config_group' => $params['db']['group_key'],
                    'config_content' => json_encode($params['private_content'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
                ], [
                    'config_content' => json_encode($params['private_content'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                    'created_at' => now()
                ]);
            }

            return ['success' => true];
        }
        catch(Exception $e)
        {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}


if (! function_exists('getRealIP')) {
    /**
     * 실제 클라이언트 IP 가져오기
     * @return mixed|string|null
     */
    function getRealIP() : mixed
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return request()->ip(); // it will return server ip when no client ip found
    }
}

if (!function_exists('arrayNullRemove')) {
    /**
     * 빈 배열값 '' 변경
     *
     * @param array $params
     * @return array
     */
    function arrayNullRemove(array $params) : array
    {
        $response = [];
        foreach($params as $key => $val) {
            $response[$key] = $val ?? '';
        }

        return $response;
    }
}

if(!function_exists('isAgent')) {
    /**
     * pc, mobile agent check
     *
     * @return mixed
     */
    function isAgent() : mixed
    {
        $agent = new Jenssegers\Agent\Agent();

        return ($agent->isDesktop()) ? 'PC' : 'MOBILE_WEB';
    }
}


if (! function_exists('isAdmin')) {
    /**
     * 관리자 유무 확인
     * @return bool
     */
    function isAdmin(): bool
    {
        try {
            return !is_null(session()->get('ADMIN_ID'));
        } catch (\Psr\Container\NotFoundExceptionInterface|\Psr\Container\ContainerExceptionInterface $e) {
            return false;
        }
    }
}

if (! function_exists('isApiAdmin')) {
    /**
     * 생텀 관리자 유무 확인
     * @return bool
     */
    function isApiAdmin(): bool
    {
        return isset(auth()->user()->admin_id);
    }
}

if (! function_exists('isApp')) {
    /**
     * 쇼핑몰 기본 설정 -> 어플리케이션 정보 -> APP ID
     * @return bool
     */
    function isApp(): bool
    {
        return (\Illuminate\Support\Str::contains(request()->header('user-agent'), getConfigJson('shopDefaultConfig')->app_id ?? 'NonApp'));
    }
}

if (! function_exists('isLoginUser')) {
    /**
     * 유저 로그인 확인 여부
     * @return bool
     */
    function isLoginUser(): bool
    {
        return !empty(getClientSession('member_id'));
    }
}

if (! function_exists('han2Byte')) {
    /**
     * 한글 2바이트로 계산
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function han2Byte(string $str) : string
    {
        return mb_strlen($str,"utf-8") + (strlen($str) - mb_strlen($str,"utf-8")) / 2;
    }
}

if (! function_exists('adminPermCheck')) {
    function adminPermCheck(int|array $id): bool
    {
        if (is_array($id)) {
            $arr = array_intersect($id, session('MENU_AUTH_IDS'));
            return count($arr) > 0;
        }

        return in_array($id, session('MENU_AUTH_IDS'), true);
    }
}


if (!function_exists('getClientSessionKey')) {
    /**
     * 클라이언트 세션 아이디
     * @return array|string|null
     */
    function getClientSessionKey(): array|string|null
    {
        try {
            return request()->header('client-session-id');
        }
        catch (Exception $e) {
            return null;
        }
    }
}

if (! function_exists('setClientSession')) {
    /**
     * 클라이언트 세션 세팅
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    function setClientSession(array $values): bool
    {
        $request = request();
        $sessionModel = new \App\Models\CmcoSession();
        try {
            $data = [];
            if ($request->header('client-session-id')) {
                $clientSessionId = $request->header('client-session-id');
                $res = $sessionModel->select('data')->where('session_id', $clientSessionId)->first();
                if ($res) {
                    $data = json_decode($res->data, true, 512, JSON_THROW_ON_ERROR);
                }

                foreach($values as $k => $v) {
                    $data[$k] = $v;
                }

                $json_data = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

                if ($res) {
                    $sessionModel->where('session_id', $clientSessionId)->update(['data' => $json_data, 'updated_at' => now()]);
                    return false;
                }

                return $sessionModel->setInsert(['session_id' => $clientSessionId, 'data' => $json_data, 'updated_at' => now(), 'created_at' => now()])['success'];
            }

            return true;
        } catch (JsonException $e) {
            return false;
        }
    }
}

if (! function_exists('getClientSession')) {
    /**
     * 클라이언트 세션 가져오기
     * @param string|array $key
     * @return mixed
     */
    function getClientSession(string|array $key): mixed
    {
        $request = request();
        try {
            $res = (new \App\Models\CmcoSession())->select('data')->where('session_id', $request->header('client-session-id'))->first();

            if(is_array($key)){
                $result = $res ? Arr::only(json_decode($res->data, true, 512, JSON_THROW_ON_ERROR), $key) : [];
            }else{
                $result = $res ? Arr::get(json_decode($res->data, true, 512, JSON_THROW_ON_ERROR), $key) : null;
            }
            return $result;
        } catch (JsonException $e) {
            return null;
        }
    }
}

if (! function_exists('forgetClientSession')) {
    /**
     * 클라이언트 세션 삭제
     * @param string $key
     * @return mixed
     */
    function forgetClientSession(string $key): mixed
    {
        $request = request();
        try {
            $sessionModel = new \App\Models\CmcoSession();
            $clientSessionId = $request->header('client-session-id');
            $res = $sessionModel->select('data')->where('session_id', $clientSessionId)->first();
            if ($res) {
                $data = json_decode($res->data, true, 512, JSON_THROW_ON_ERROR);
                unset($data[$key]);

                $sessionModel->where('session_id', $clientSessionId)->update(['data' => json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), 'updated_at' => now()]);
            }

            return true;
        } catch (JsonException $e) {
            return null;
        }
    }
}


if (! function_exists('clearClientSession')) {
    /**
     * 클라이언트 모두 삭제
     * @return mixed
     */
    function clearClientSession(): mixed
    {
        $request = request();
        try {
            $sessionModel = new \App\Models\CmcoSession();
            $clientSessionId = $request->header('client-session-id');
            $sessionModel->where('session_id', $clientSessionId)->update(['data' => json_encode([], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), 'updated_at' => now()]);

            return true;
        } catch (JsonException $e) {
            return null;
        }
    }
}

if (! function_exists('getAppGlobalData')) {
    /**
     * 글로벌 변수로 지정된 데이터 로드
     * @param string $key
     * @return array|ArrayAccess|mixed|null
     */
    function getAppGlobalData(string $key): mixed
    {
        try {
            return app('shopConfigs')[$key];
        } catch (\Illuminate\Contracts\Container\BindingResolutionException $e) {
            (new App\Http\Controllers\Controller());
            try {
                return app('shopConfigs')[$key];
            } catch (Exception $e) {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }
}

if (! function_exists('holidayPassDate')) {
    function holidayPassDate($date, $diff) {
        $sign = $diff < 0 ? "-" : "+";

        do {
            $date = date("Y-m-d", strtotime("{$sign}1 days", strtotime($date)));
            if ((date("w", strtotime($date)) != 0 && date("w", strtotime($date)) != 6)) {
                $diff = $sign === "-" ? $diff + 1 : $diff - 1;
            }
        } while ($diff != 0);

        return date("m월 d일", strtotime($date));
    }
}

if (! function_exists('generateRandomString')) {
    function generateRandomString(string $str, int $length)
    {
        $characters = $str;
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

if(!function_exists('priceUnitCut')) {
    /**
     * @brief 가격 원단위 처리
     * @param float $price
     * @param int $unit
     * @param string $type
     * @return float
     */
    function priceUnitCut(float $price, int $unit, string $type = 'down') : float
    {
        $num = $price;
        switch ($type) {
            case 'down' :
                $num = floor($price/$unit) * $unit;
                break;
            case 'up' :
                $num = ceil($price/$unit) * $unit;
                break;
            case 'round' :
                $num = round($price/$unit) * $unit;
                break;
        }

        return $num;
    }
}

if(!function_exists('publicImagePath')) {
    /**
     * 퍼블릭 이미지 패스 지정
     * @param string $image
     * @return string
     */
    function publicImagePath(?string $image = '') : string
    {
        return ($image) ? env('PUBLIC_CDN_URL').$image : '';
    }
}

if(!function_exists('productImagePath')) {
    /**
     * 상품 이미지 패스 지정
     * @param string $image
     * @return string
     */
    function productImagePath(?string $image = '') : string
    {
        return ($image) ? env('PRODUCT_CDN_URL').$image : '';
    }
}

if(!function_exists('priceUnitKo')){

    /**
     * 금액 단위 한글
     * @param float $num
     * @return string
     */
    function priceUnitKo(float $price){

        if( $price<0 || empty($price) ) $price = 0;

        $priceUnit = array('원', '만원', '억', '조', '경');
        $expUnit = 10000;
        $resultArray = array();
        $result = "";

        foreach($priceUnit as $k => $v){
            $unitResult = ( $price % pow($expUnit,$k+1) ) / (pow($expUnit, $k));
            $unitResult = floor($unitResult);

            if($unitResult>0){
                $resultArray[$k] = $unitResult;
            }
        }

        if(count($resultArray)>0){
            foreach($resultArray as $k => $v){
                $result = $v.$priceUnit[$k].''.$result;
            }
        }

        return $result;
    }

    if (! function_exists('resizeImage')) {
        function resizeImage($file, $dst_w, $dst_h, $crop=FALSE) {
            list($width, $height) = getimagesize($file);
            $ratio = $width / $height;
            if ($crop) {
                if ($width > $height) {
                    $width = ceil($width-($width*abs($ratio-$dst_w/$dst_h)));
                } else {
                    $height = ceil($height-($height*abs($ratio-$dst_w/$dst_h)));
                }
                $newwidth = $dst_w;
                $newheight = $dst_h;
            } else {
                if ($dst_w/$dst_h > $ratio) {
                    $newwidth = $dst_h*$ratio;
                    $newheight = $dst_h;
                } else {
                    $newheight = $dst_w/$ratio;
                    $newwidth = $dst_w;
                }
            }

            $exploding = explode(".",$file);
            $ext = end($exploding);
            switch($ext){
                case "png":
                    $src = imagecreatefrompng($file);
                    break;
                case "jpeg":
                case "jpg":
                    $src = imagecreatefromjpeg($file);
                    break;
                case "gif":
                    $src = imagecreatefromgif($file);
                    break;
                default:
                    $src = imagecreatefromjpeg($file);
                    break;
            }

            $result = imagecreatetruecolor($newwidth, $newheight);
            imagecopyresampled($result, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
            return array($result, $ext);
        }
    }
}

if (!function_exists('calculateCutPrice')) {
    /**
     * @param int $price
     * @param string $unit_type
     * @return int|string
     * 쇼핑몰 절삭 단위
     */
    function calculateCutPrice(int $price = 0, string $unit_type = '') : mixed
    {
        $unitConfig = getAppGlobalData('unitConfig');
        if($unit_type == 'product'){
            $cutUnit = $unitConfig->product_cut_unit * 10;
            $decimal = $unitConfig->product_decimal;
        }else if($unit_type == 'point'){
            $cutUnit = $unitConfig->point_cut_unit * 10;
            $decimal = $unitConfig->point_decimal;
        }else if($unit_type == 'coupon'){
            $cutUnit = $unitConfig->coupon_cut_unit * 10;
            $decimal = $unitConfig->coupon_decimal;
        }else if($unit_type == 'grade'){
            $cutUnit = $unitConfig->grade_cut_unit * 10;
            $decimal = $unitConfig->grade_decimal;
        }else{
            return $price;
        }

        $price_law = 0;

        if (strtoupper($decimal)) {
            switch (strtoupper($decimal)) {
                case 'CEIL': // 올림
                    $price_law =  ceil($price/$cutUnit)*$cutUnit;
                    break;
                case 'FLOOR': // 내림
                    $price_law =  floor($price/$cutUnit)*$cutUnit;
                    break;
                case 'ROUND': // 반올림
                    $price_law =  round($price/$cutUnit)*$cutUnit;
                    break;
            }
        }

        return $price_law;
    }
}

if (!function_exists('sizeChangeSort')) {
    function sizeChangeSort(array $arr)
    {
        $define = [
            'xxxs',
            'xxs',
            'xs',
            's',
            'm',
            'l',
            'xl',
            'xxl',
            'xxxl',
            'xxxxl',
        ];

        $temp = [];
        foreach($arr as $idx=>$str){
            $txt = preg_replace('/[0-9]/', '', $str);
            $temp[$idx] = $str;
            if($txt){
                $key = array_search(strtolower($txt), $define);
                if($key){
                    $str = str_replace($txt, $key, $str);
                }
            }
            $arr[$idx] = $str;
        }

        natsort($arr);

        $result = [];
        foreach($arr as $idx => $str){
            $result[] = $temp[$idx];
        }

        return $result;
    }
}

/**
 * 문자열 암호화(복호화 가능한 암호화)
 */
if (!function_exists('stringCrypt')) {
    function stringEncrypt(string $str)
    {
        return Crypt::encryptString(base64_encode($str));
    }
}


/**
 * 문자열 복호화(복호화 가능한 암호화)
 */
if (!function_exists('stringDecrypt')) {
    function stringDecrypt(string $str)
    {
        return base64_decode(Crypt::decryptString($str));
    }
}




/**
 *  디버그바 노출
**/
if (!function_exists('debugbar')) {
    /**
     * Debugbar helper function.
     *
     * @return \Barryvdh\Debugbar\LaravelDebugbar
     */
    function debugbar()
    {
        return app('debugbar');
    }
}

if (!function_exists('debug_info')) {
    function debug_info($message)
    {
        if (config('app.debug') === true && app()->bound('debugbar')) {
            return app('debugbar')->info($message);
        }
        return null;
    }
}

if (!function_exists('debug_error')) {
    function debug_error($message)
    {
        if (config('app.debug') === true && app()->bound('debugbar')) {
            return app('debugbar')->error($message);
        }
        return null;
    }
}


if (!function_exists('format_date')) {
    /**
     * 날짜를 지정된 포맷으로 변환합니다.
     *
     * @param string|null $date
     * @param string $format
     * @return string
     */
    function format_date($date, $format = 'Y-m-d')
    {
        //$format = 'Y-m-d H:i';
        if (empty($date)) {
            return '-';
        }
        return Carbon\Carbon::parse($date)->format($format);
    }
}

if (!function_exists('handle_validation_response')) {
    /**
     * 유효성 검사 실패 시 응답을 처리합니다.
     */
    function handle_validation_response(Request $request, $validator, ?string $redirectRoute = '', array $routeParams = [])
    {
        debug_info('**************D:\cms\app\Helpers\Helpers.php ************ handle_validation_response');
        $errors = is_array($validator) ? $validator : $validator->errors();
        // AJAX 요청인 경우 JSON 응답 반환
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'errors' => $errors
            ], 422);
        }

        // 일반 요청인 경우 리다이렉트
        return redirect()->route($redirectRoute, $routeParams)
            ->withErrors($errors)
            ->withInput();

    }
}


if (!function_exists('handle_success_response')) {
    /**
     * 성공 응답을 처리합니다.
     */
    function handle_success_response(Request $request, ?string $message = null, ?string $redirectRoute = null, array $routeParams = [],  array $data = [])
    {
        debug_info('**************D:\cms\app\Helpers\Helpers.php ************ handle_success_response');
        // AJAX 요청인 경우 JSON 응답 반환
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route($redirectRoute, $routeParams),
                'data' => $data
            ], 200);
        }

        // 일반 요청인 경우 리다이렉트
        return redirect()->route($redirectRoute, $routeParams)
            ->with('success', $message);
    }
}





if (!function_exists('handle_error_response')) {
    /**
     * 실패 응답을 처리합니다.
     */
    function handle_error_response(Request $request, ?string $message = null, ?string $redirectRoute = null, array $routeParams = [],  array $data = [])
    {
        debug_info('**************D:\cms\app\Helpers\Helpers.php ************ handle_success_response');
        // AJAX 요청인 경우 JSON 응답 반환
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'redirect' => route($redirectRoute, $routeParams),
                'data' => $data
            ], 500);
        }

        // 일반 요청인 경우 리다이렉트
        return redirect()->route($redirectRoute, $routeParams)
            ->with('error', $message);
    }
}


if (! function_exists('public_delete')) {
    /**
     * public 디스크에서 storage URL → 상대경로로 변환 후,
     * 파일이 존재할 때만 삭제 처리
     *
     * @param  string|string[]  $paths
     * @return bool|array
     */
    function public_delete($paths)
    {
        $disk = Storage::disk('public');

        // storage/ 또는 /storage/ 접두사 제거 + 좌측 슬래시 제거
        $normalize = function(string $path): string {
            // 'storage/' 이후 문자열만 취함
            $relative = Str::after($path, 'storage/');
            // ltrim 으로 선행 슬래시 제거
            return ltrim($relative, '/');
        };

        // 여러 경로 처리
        if (is_array($paths)) {
            $results = [];
            foreach ($paths as $path) {
                $relative = $normalize($path);
                // 존재할 때만 삭제
                if ($disk->exists($relative)) {
                    $results[$path] = $disk->delete($relative);
                } else {
                    $results[$path] = false;
                }
            }
            return $results;
        }

        // 단일 경로 처리
        $relative = $normalize($paths);
        if ($disk->exists($relative)) {
            return $disk->delete($relative);
        }

        return false;
    }
}

if (! function_exists('renderContentAllowHtmlButEscapeScript')) {
    /**
     * <script> 태그만 이스케이프하고, 나머지 지정 태그는 허용하여 렌더링
     */
    function renderContentAllowHtmlButEscapeScript(string $html): string
    {
        // 1) <script>…</script>만 엔티티 이스케이프
        $escaped = preg_replace_callback(
            '#<script\b([^>]*)>(.*?)</script>#is',
            function(array $m) {
                return htmlspecialchars($m[0], ENT_QUOTES | ENT_HTML5);
            },
            $html
        );

        // 2) 허용할 태그 목록 (필요에 따라 추가)
        $allowed = '<p><br><strong><em><ul><ol><li><figure><img><a>';
        return strip_tags($escaped, $allowed);
    }
}

if (!function_exists('safe_decrypt')) {
    /**
     * 안전하게 암호화된 값을 복호화합니다.
     *
     * @param  string|null  $value
     * @return string|null
     */
    function safe_decrypt($value)
    {
        if (!$value) {
            return $value;
        }

        try {
            return decrypt($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return $value; // 복호화 실패 시 원본 값 반환
        }
    }
}
if (!function_exists('renderNavigation')) {
    function renderNavigation($menuItems) {
        if (empty($menuItems)) return '';

        $html = '<ul>';

        foreach ($menuItems as $menu) {
            $html .= '<li>';
            $html .= '<a href="' . htmlspecialchars($menu->path) . '">' . htmlspecialchars($menu->title) . '</a>';

            if (!empty($menu->children)) {
                $html .= '<div class="dropdown">';
                $html .= '<div class="left">' . htmlspecialchars($menu->title) . '</div>';
                $html .= '<div class="right">';
                $html .= renderSubMenu($menu->children, 2);
                $html .= '</div>';
                $html .= '</div>';
            }

            $html .= '</li>';
        }


        if(session()->has('blot_mbid') && session()->get('blot_mbid')) {
            $html .= ' <a href="' . route('front_member.show', session()->get('blot_mbid') ) . '" class="logout"> ';
            $html .= '    <span>회원정보수정</span>';
            $html .= '</a>';
            $html .= '<a href="' . route('logout') . '" class="logout">';
            $html .= '  <span>로그아웃</span>';
            $html .= '</a>';
        } else {
            $html .= '<a href="' . route('front_member.create') . '">';
            $html .= '   <span>회원가입</span>';
            $html .= '</a>';
            $html .= '<a href="' . route('login') . '">';
            $html .= '   <span>로그인</span>';
            $html .= '</a>';
        }

        $html .= '</ul>';


        return $html;
    }
}

if (!function_exists('renderSubMenu')) {
    function renderSubMenu($menuItems, $depth) {
        if (empty($menuItems)) return '';

        $depthClass = 'depth_' . $depth;
        $html = '<ul class="' . $depthClass . '">';

        foreach ($menuItems as $menu) {
            $html .= '<li>';
            $html .= '<a href="' . htmlspecialchars($menu->path) . '">' . htmlspecialchars($menu->title) . '</a>';

            if (!empty($menu->children)) {
                $html .= renderSubMenu($menu->children, $depth + 1);
            }

            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }
}



if (!function_exists('renderFooter')) {
    function renderFooter($footerItems) {
        if (empty($footerItems)) return '';
        $html = ' <ul role="list" class="info">';

        foreach ($footerItems as $item) {
            $html .= '<li role="listitem">';
            $html .= '<p><span>' . htmlspecialchars($item['title']) . '</span>';
            $html .=  htmlspecialchars($item['content']) . '</p>';
            $html .= '</li>';
        }
        $html .= '</ul>';


        return $html;
    }
}


if (!function_exists('renderSNS')) {
    function renderSNS($snsItems) {
        if (empty($snsItems)) return '';
        $html = ' <ul role="list" class="info">';

        foreach ($snsItems as $item) {
            $name = isset($item['name']) ? htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') : '';
            $link = isset($item['link']) ? htmlspecialchars($item['link'], ENT_QUOTES, 'UTF-8') : '';

            if (!empty($name) && !empty($link)) {
                $html .= '<li role="listitem">';
                $html .= '<p><span>' . $name . '</span>';
                $html .= '<a href="' . $link . '" target="_blank" rel="noopener noreferrer">' . $link . '</a>';
                $html .= '</p>';
                $html .= '</li>';
            }
        }
        $html .= '</ul>';
        return $html;
    }
}


/**
 * 경고 메시지 출력 후 지정된 라우트로 이동
 */
function alertAndRedirect($message, $route, $params = [])
{
    $url = route($route, $params);
    $script = "
        <script>
            alert('{$message}');
            window.location.href = '{$url}';
        </script>
    ";
    return response($script);
}

/**
 * 경고 메시지 출력 후 URL로 이동
 */
function alertAndRedirectUrl($message, $url)
{
    $script = "
        <script>
            alert('{$message}');
            window.location.href = '{$url}';
        </script>
    ";
    return response($script);
}

/**
 * 경고 메시지 출력 후 이전 페이지로 이동
 */
function alertAndGoBack($message)
{
    $script = "
        <script>
            alert('{$message}');
            history.back();
        </script>
    ";
    return response($script);
}

/**
 * 확인/취소 다이얼로그 후 페이지 이동
 */
function confirmAndRedirect($message, $route, $params = [])
{
    $url = route($route, $params);
    $script = "
        <script>
            if(confirm('{$message}')) {
                window.location.href = '{$url}';
            } else {
                history.back();
            }
        </script>
    ";
    return response($script);
}
