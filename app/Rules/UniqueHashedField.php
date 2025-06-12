<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueHashedField implements Rule
{
    protected $table;
    protected $hashColumn;
    protected $ignoreId;
    protected $message;

    public function __construct($table, $hashColumn, $ignoreId = null)
    {
        $this->table = $table;
        $this->hashColumn = $hashColumn;
        $this->ignoreId = $ignoreId;
    }

    public function passes($attribute, $value)
    {
        $hash = hash('sha256', $value);

        $query = DB::table($this->table)->where($this->hashColumn, $hash);

        // 수정시 현재 레코드 제외
        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        $exists = $query->exists();

        // 메시지 설정
        $this->message = $exists ? '이미 사용중인 ' . $this->getFieldName($attribute) . '입니다.' : null;

        return !$exists;
    }

    public function message()
    {
        return $this->message ?: '이미 사용중인 값입니다.';
    }

    private function getFieldName($attribute)
    {
        $fieldNames = [
            'email' => '이메일',
            'phone' => '전화번호',
            'user_name' => '사용자명',
        ];

        return $fieldNames[$attribute] ?? $attribute;
    }
}
