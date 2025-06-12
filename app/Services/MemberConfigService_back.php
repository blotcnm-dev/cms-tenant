<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MemberConfigService_back
{
    /**
     * 모든 회원 설정 가져오기
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllConfig()
    {
        // 캐싱 사용 (선택적)
        return Cache::remember('user_all_config', 60*24, function () {
            return DB::table('bl_config')
                ->where('code_group', 'user')
                ->orderBy('config_id', 'asc')
                ->get();
        });

        // 캐싱 없이 직접 DB 조회
        // return DB::table('bl_config')
        //     ->where('code_group', 'user')
        //     ->orderBy('config_id', 'asc')
        //     ->get();
    }



    public function getAllGrade()
    {
        // 캐싱 사용 (선택적)
        return Cache::remember('grade_all_config', 60*24, function () {

            return DB::table('bl_config')
                ->select(DB::raw('code_name, code'))
                ->where('code_group', 'member')
                ->orderBy('config_id', 'asc')
                ->get();

        });

        // 캐싱 없이 직접 DB 조회
        // return DB::table('bl_config')
        //     ->where('code_group', 'user')
        //     ->orderBy('config_id', 'asc')
        //     ->get();
    }

    public function getGradeName($code)
    {
        $result = DB::table('bl_config')
            ->select(DB::raw('code_name'))
            ->where('code_group', 'member')
            ->where('code', $code)
            ->first();

        return $result ? $result->code_name : '';
    }



    /**
     * 기본 설정 필드 가져오기 (프로필이미지 & 추가설정 제외)
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBasicConfig()
    {
        return $this->getAllConfig()->filter(function($item) {
            return $item->code !== 'user_etc' && $item->code !== 'profile_image';
        });
    }

    /**
     * 프로필 이미지 설정 가져오기
     *
     * @return mixed
     */
    public function getProfileConfig()
    {
        return $this->getAllConfig()->where('code', 'profile_image');
    }

    /**
     * 추가 설정 가져오기
     *
     * @return mixed
     */
    public function getEtcConfig()
    {
        return $this->getAllConfig()->where('code', 'user_etc');
    }

    /**
     * 필수 입력 필드 정보 가져오기
     *
     * @return array
     */
    public function getRequiredFields()
    {
        $allConfig = $this->getAllConfig();

        // 기본 필드 중 필수 입력 설정 가져오기
        $requiredMap = $allConfig->filter(function($item) {
            return $item->use == '1' && $item->sort == '1';
        })->pluck('code_name', 'code')->toArray();


        // 추가 필드 중 필수 입력 설정 가져오기
        $etcConfig = $allConfig->where('code', 'user_etc')->first();

        if (isset($etcConfig->value) && !empty($etcConfig->value)) {
            $etcConfigData = json_decode($etcConfig->value);

            if (json_last_error() === JSON_ERROR_NONE && is_array($etcConfigData)) {
                foreach ($etcConfigData as $item) {
                    if (isset($item->is_required) && $item->is_required === true) {
                        $fieldId = 'field' . $item->name_en;
                        $requiredMap[$fieldId] = $item->name_kr;
                    }
                }
            }
        }

        return $requiredMap;
    }

    /**
     * 기본 필드 HTML 생성
     *
     * @return array
     */
    public function generateBasicFields__()
    {
        $basicConfig = $this->getBasicConfig();
        $basic_fields = [];

        foreach ($basicConfig as $key => $item) {
            $code_info1 = $item->code_info1 ?? '';
            $basic_fields[$key] = $this->generateField(
                $item->use,
                $item->code,
                $item->code,
                $item->code_name,
                $item->sort,
                $item->code_name.'를 입력하세요',
                $item->value,
                $code_info1
            );
        }

        return $basic_fields;
    }

 /**
 * 기본 필드 HTML 생성 (신규 등록 및 수정 모두 지원)
 *
 * @param array|null $userData 사용자 데이터 (수정 시 제공, 기본값은 null)
 * @param array $attributes 추가 속성 (기본값은 빈 배열)
 * @param bool $readonly 읽기 전용 여부 (기본값은 false)
 * @return array
 */
    public function generateBasicFields($userData = null, array $attributes = [], bool $readonly = false,  bool $excludePassword = false)
    {
        $basicConfig = $this->getBasicConfig();
        $basic_fields = [];


        foreach ($basicConfig as $key => $item) {

            // 비밀번호 필드 제외 옵션이 활성화되어 있고, 현재 필드가 비밀번호인 경우 건너뛰기
            if ($excludePassword && $item->code === 'password') {
                continue;
            }

            $code_info1 = $item->code_info1 ?? '';

            // 사용자 데이터가 있으면 해당 값을 사용, 없으면 빈 값 사용
            $value = '';
            if ($userData && isset($userData[$item->code])) {
                $value = $userData[$item->code];
            }

            $basic_fields[$key] = $this->generateField(
                $item->use,
                $item->code,
                $item->code,
                $item->code_name,
                $item->sort,
                $item->code_name.'를 입력하세요',
                $item->value,
                $code_info1,
                $value,
                $attributes,
                $readonly
            );
        }

        return $basic_fields;
    }
    /**
     * 추가 필드 HTML 생성 (신규 등록 및 수정 모두 지원)
     *
     * @param array|null $userData 사용자 데이터 (수정 시 제공, 기본값은 null)
     * @param array $attributes 추가 속성 (기본값은 빈 배열)
     * @param bool $readonly 읽기 전용 여부 (기본값은 false)
     * @return array
     */
    public function generateEtcFields($userData = null, array $attributes = [], bool $readonly = false)
    {
        $etcConfig = $this->getEtcConfig()->first();

        $etc_fields = [];

        if (isset($etcConfig->value) && !empty($etcConfig->value)) {
            $etcConfigData = json_decode($etcConfig->value);

            if (json_last_error() === JSON_ERROR_NONE && is_array($etcConfigData)) {
                foreach ($etcConfigData as $item) {
                    if (!isset($item->name_en) || !isset($item->name_kr) || !isset($item->field_type)) {
                        continue;
                    }

                    // 고유한 식별자 생성
                    $fieldId = 'field' . $item->name_en;
                    $fieldName = 'field' . $item->etc_no;

                    // 사용자 데이터가 있으면 해당 값을 사용
                    $value = '';
                    if ($userData && isset($userData[$fieldName])) {
                        $value = $userData[$fieldName];
                    }

                    $etc_fields[$fieldId] = $this->generateField(
                            $item->is_active ?? false ? '1' : '0',
                        $fieldId,
                        $fieldName,
                        $item->name_kr,
                            $item->is_required ?? false ? '1' : '0',
                        $item->name_kr.'를 입력하세요',
                        $item->field_type,
                        $item->options ?? '',
                        $value,
                        $attributes,
                        $readonly
                    );
                }
            }
        }

        return $etc_fields;
    }
    /**
     * 추가 필드 HTML 생성
     *
     * @return array
     */
    public function generateEtcFields__()
    {
        $etcConfig = $this->getEtcConfig()->first();
        $etc_fields = [];

        if (isset($etcConfig->value) && !empty($etcConfig->value)) {
            $etcConfigData = json_decode($etcConfig->value);

            if (json_last_error() === JSON_ERROR_NONE && is_array($etcConfigData)) {
                foreach ($etcConfigData as $item) {
                    if (!isset($item->name_en) || !isset($item->name_kr) || !isset($item->field_type)) {
                        continue;
                    }

                    // 고유한 식별자 생성
                    $fieldId = 'field' . $item->name_en;
                    $fieldName = 'field' . $item->etc_no;

                    $etc_fields[$fieldId] = $this->generateField(
                            $item->is_active ?? false ? '1' : '0',
                        $fieldId,
                        $fieldName,
                        $item->name_kr,
                            $item->is_required ?? false ? '1' : '0',
                        $item->name_kr.'를 입력하세요',
                        $item->field_type,
                        $item->options ?? ''
                    );
                }
            }
        }

        return $etc_fields;
    }

    /**
     * 필드 HTML 생성
     *
     * @param bool|string $use 사용여부
     * @param string $id 필드 ID
     * @param string $name 필드 이름
     * @param string $label 필드 레이블
     * @param bool|string $required 필수 여부
     * @param string $placeholder 플레이스홀더 텍스트
     * @param string $fieldType 필드 타입 (input, textarea, radio 등)
     * @param string $options 추가 옵션 (라디오/체크박스/셀렉트박스 옵션 콤마 구분)
     * @return string HTML 마크업
     */
    public function generateField__($use, string $id, string $name, string $label, $required, string $placeholder, string $fieldType, string $options = '')
    {
        $html = '';
        if($use && $use !== '0'){
            $html = '<div class="input_item half">';
            $html .= '<label class="input_title" for="' . $id . '">' . $label . ($required && $required !== '0' ? ' <span class="text-danger">*</span>' : '') . '</label>';
            $html .= '<div class="inner_box">';

            // 기본 속성 설정
            $attrs = '';
            $value = '';
            $attributes = [];
            $readonly = false;
            foreach ($attributes as $key => $val) {
                $attrs .= ' ' . $key . '="' . $val . '"';
            }

            // readonly 속성 추가
            $readonlyAttr = $readonly ? ' readonly' : '';

            // 필드 타입에 따라 다른 입력 요소 생성
            switch ($fieldType) {
                case 'input':
                    $html .= '<input type="text" class="common_input" id="' . $id . '" name="' . $name . '" placeholder="' . $placeholder . '" value="' . $value . '" ' . $readonlyAttr . $attrs . '>';
                    break;

                case 'password':
                    $html .= '<input type="password" class="common_input" id="' . $id . '" name="' . $name . '" placeholder="' . $placeholder . '"' . $readonlyAttr . $attrs . '>';
                    break;

                case 'textarea':
                    $html .= '<div class="textarea_count">';
                    $html .= '<textarea class="common_textarea" id="' . $id . '" name="' . $name . '" placeholder="' . $placeholder . '"' . $readonlyAttr . $attrs . '>' . $value . '</textarea>';
                    $html .= '<p><span>0</span> / 200</p>';
                    $html .= '</div>';
                    break;

                case 'radio':
                    $html .= '<div class="flex gap_input">';
                    if (!empty($options)) {
                        $optionList = explode(',', $options);
                        foreach ($optionList as $index => $option) {
                            $option = trim($option);
                            $isChecked = $value == $option ? ' checked' : '';

                            $html .= '<label class="radio_input">';
                            $html .= '<input type="radio" value = "'.$option.'" name="' . $name . '"  ' . $isChecked . $readonlyAttr . $attrs . '>';
                            $html .= '<span>' . $option . '</span>';
                            $html .= '</label>';
                        }
                    }
                    $html .= '</div>';
                    break;

                case 'checkbox':
                    $html .= '<label class="chk_input">';
                    $html .= '<input type="checkbox"   name="' . $name . '"' . ($value ? ' checked' : '') . $readonlyAttr . $attrs . '>';
                    $html .= '<span>' . $placeholder . '</span>';
                    $html .= '</label>';
                    break;

                case 'selectbox':
                    $html .= '<div class="custom_select_1 js_custom_select">';
                    $html .= '<input type="text" class="common_input select_value" placeholder="' . $placeholder . '" data-value="' . $value . '" value="' . $value . '" readonly>';
                    $html .= '<ul role="list">';

                    if (!empty($options)) {
                        $optionList = explode(',', $options);
                        foreach ($optionList as $option) {
                            $option = trim($option);
                            $dataValue = strtolower(str_replace(' ', '_', $option));
                            $html .= '<li role="listitem" data-value="' . $dataValue . '">' . $option . '</li>';
                        }
                    }

                    $html .= '</ul>';
                    $html .= '</div>';
                    break;

                default:
                    $html .= '<input type="text" class="common_input" id="' . $id . '" name="' . $name . '" placeholder="' . $placeholder . '" value="' . $value . '"' . $readonlyAttr . $attrs . '>';
                    break;
            }

            $html .= '</div>';
            $html .= '<div id="'.$id.'-error" class="error_msg"></div>';

            $html .= '</div>';
        }
        return $html;
    }

    /**
     * 필드 HTML 생성 (신규 등록 및 수정 모두 지원)
     *
     * @param bool|string $use 사용여부
     * @param string $id 필드 ID
     * @param string $name 필드 이름
     * @param string $label 필드 레이블
     * @param bool|string $required 필수 여부
     * @param string $placeholder 플레이스홀더 텍스트
     * @param string $fieldType 필드 타입 (input, textarea, radio 등)
     * @param string $options 추가 옵션 (라디오/체크박스/셀렉트박스 옵션 콤마 구분)
     * @param mixed $value 필드 값 (사용자 데이터, 기본값은 빈 문자열)
     * @param array $attributes 추가 속성 (기본값은 빈 배열)
     * @param bool $readonly 읽기 전용 여부 (기본값은 false)
     * @return string HTML 마크업
     */
    public function generateField(
        $use,
        string $id,
        string $name,
        string $label,
        $required,
        string $placeholder,
        string $fieldType,
        string $options = '',
        $value = '',
        array $attributes = [],
        bool $readonly = false
    ) {
        $html = '';
        if($use && $use !== '0'){
            $html = '<div class="input_item half">';
            $html .= '<label class="input_title" for="' . $id . '">' . $label . ($required && $required !== '0' ? ' <span class="text-danger">*</span>' : '') . '</label>';
            $html .= '<div class="inner_box">';

            // 기본 속성 설정
            $attrs = '';
            foreach ($attributes as $key => $val) {
                $attrs .= ' ' . $key . '="' . $val . '"';
            }

            // readonly 속성 추가
            $readonlyAttr = $readonly ? ' readonly' : '';

            // 필드 타입에 따라 다른 입력 요소 생성
            switch ($fieldType) {
                case 'input':
                    $html .= '<input type="text" class="common_input" id="' . $id . '" name="' . $name . '" placeholder="' . $placeholder . '" value="' . htmlspecialchars($value) . '" ' . $readonlyAttr . $attrs . '>';
                    break;

                case 'password':
                    $html .= '<input type="password" class="common_input" id="' . $id . '" name="' . $name . '" placeholder="' . $placeholder . '"' . $readonlyAttr . $attrs . '>';
                    break;

                case 'textarea':
                    $html .= '<div class="textarea_count">';
                    $html .= '<textarea class="common_textarea" id="' . $id . '" name="' . $name . '" placeholder="' . $placeholder . '"' . $readonlyAttr . $attrs . '>' . htmlspecialchars($value) . '</textarea>';
                    $charCount = is_string($value) ? mb_strlen($value) : 0;
                    $html .= '<p><span>' . $charCount . '</span> / 200</p>';
                    $html .= '</div>';
                    break;

                case 'radio':
                    $html .= '<div class="flex gap_input">';
                    if (!empty($options)) {
                        $optionList = explode(',', $options);
                        foreach ($optionList as $index => $option) {
                            $option = trim($option);
                            $isChecked = $value == $option ? ' checked' : '';

                            $html .= '<label class="radio_input">';
                            $html .= '<input type="radio" value="'.$option.'" name="' . $name . '"' . $isChecked . $readonlyAttr . $attrs . '>';
                            $html .= '<span>' . $option . '</span>';
                            $html .= '</label>';
                        }
                    }
                    $html .= '</div>';
                    break;

                case 'checkbox':
                    $isChecked = $value ? ' checked' : '';
                    $html .= '<label class="chk_input">';
                    $html .= '<input type="checkbox" name="' . $name . '"' . $isChecked . $readonlyAttr . $attrs . '>';
                    $html .= '<span>' . $placeholder . '</span>';
                    $html .= '</label>';
                    break;

                case 'selectbox':
                    $dataValue = is_string($value) ? strtolower(str_replace(' ', '_', $value)) : '';
                    $html .= '<div class="custom_select_1 js_custom_select">';
                    $html .= '<input type="text" class="common_input select_value" placeholder="' . $placeholder . '" data-value="' . $dataValue . '" value="' . htmlspecialchars($value) . '" readonly>';
                    $html .= '<ul role="list">';

                    if (!empty($options)) {
                        $optionList = explode(',', $options);
                        foreach ($optionList as $option) {
                            $option = trim($option);
                            $optionDataValue = strtolower(str_replace(' ', '_', $option));
                            $selected = $value == $option ? ' class="selected"' : '';
                            $html .= '<li role="listitem" data-value="' . $optionDataValue . '"' . $selected . '>' . $option . '</li>';
                        }
                    }

                    $html .= '</ul>';
                    $html .= '</div>';
                    break;

                default:
                    $html .= '<input type="text" class="common_input" id="' . $id . '" name="' . $name . '" placeholder="' . $placeholder . '" value="' . htmlspecialchars($value) . '"' . $readonlyAttr . $attrs . '>';
                    break;
            }

            $html .= '</div>';
            $html .= '<div id="'.$id.'-error" class="error_msg"></div>';

            $html .= '</div>';
        }
        return $html;
    }


    /**
     * 사용자 설정 저장
     *
     * @param string $customFields JSON 형식의 커스텀 필드 데이터
     * @return bool
     */
    public function saveCustomFields($customFields)
    {
        try {
            DB::table('bl_members')
                ->updateOrInsert(
                    [
                        'code_group' => 'user',
                        'code' => 'user_etc'
                    ],
                    [
                        'value' => $customFields,
                        'updated_at' => now()
                    ]
                );

            // 캐시 갱신 (캐싱 사용 시)
            Cache::forget('user_all_config');

            return true;
        } catch (\Exception $e) {
            \Log::error('Custom fields save error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 캐시 초기화
     */
    public function clearCache()
    {
        Cache::forget('user_all_config');
    }
}
