<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BLConfig extends Model
{
    use HasFactory;

    /**
     * 연결할 테이블 명
     *
     * @var string
     */
    protected $table = 'bl_config';

    /**
     * 기본 키 설정
     *
     * @var string
     */
    protected $primaryKey = 'config_id';

    /**
     * 대량 할당 가능한 속성들
     *
     * @var array
     */
    protected $fillable = [
        'code_group',
        'code_type',
        'code',
        'sub_code',
        'code_name',
        'code_name_en',
        'use',
        'sort',
        'sub_sort',
        'code_info1',
        'code_info2',
        'code_info3',
        'code_info4',
    ];

    /**
     * 속성에 대한 기본값 설정
     *
     * @var array
     */
    protected $attributes = [
        'use' => 1,
        'sort' => 1,
    ];

    /**
     * 속성 캐스팅
     *
     * @var array
     */
    protected $casts = [
        'use' => 'boolean',
        'sort' => 'integer',
        'sub_sort' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 사용중인 설정만 가져오는 스코프
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('use', 1);
    }

    /**
     * 코드 그룹으로 필터링하는 스코프
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $codeGroup
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCodeGroup($query, $codeGroup)
    {
        return $query->where('code_group', $codeGroup);
    }

    /**
     * 코드 타입으로 필터링하는 스코프
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $codeType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCodeType($query, $codeType)
    {
        return $query->where('code_type', $codeType);
    }

    /**
     * 정렬 순서대로 가져오는 스코프
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort', 'asc')->orderBy('sub_sort', 'asc');
    }


}
