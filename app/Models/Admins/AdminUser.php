<?php

namespace App\Models\Admins;

use App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    use HasFactory, ModelTrait, HasApiTokens;

    public $table = "bl_members";
    public $tableKoNm = "관리자정보";
    public $primaryKey = 'member_id';
    public $timestamps = false;
    protected $guarded = [];    //대량할당 해제
    protected $hidden = [
        'password',
    ];

    public function getAdminUser(array $params, bool $paginate = false) :array
    {
        try {
            $query = $this->leftjoin('bl_codes as code_user_type', function($join) {
                $join->on('code_user_type.code', '=', 'bl_admin_users.user_type');
                $join->where('code_user_type.code_group', '=', 'vendor_type');
            });

            // 구분은 무조건 포함
            if (isset($params['user_type'])) {
                $query = $query->where($this->table . '.user_type', $params['user_type']);
            }

            // 미삭제 부분만
            $query = $query->where($this->table . '.is_del', '0');

            // 구분
            if (isset($params['user_type']) && !empty($params['user_type'])) {
                $query = $query->where($this->table . '.user_type', $params['user_type']);
            }

            // 정렬
            if (isset($params['order']) && is_array($params['order'])) {
                foreach($params['order'] as $orderby) {
                    //dump($orderby);
                    $query = $query->orderBy($orderby[0], $orderby[1]);
                }
            } else if (isset($params['order']) && isset($params['by'])) {
                if (!empty($params['order']) && !empty($params['by'])) {
                    $query = $query->orderBy($params['order'], $params['by']);
                }
            } else if (isset($params['order']) && $params['order'] === 'random') {
                $query = $query->inRandomOrder();
            }

            //키워드 검색
            if (!empty($params['search_keyword']) && !empty($params['search_type'])) {
                // 일반검색
                $query = $query->where($params['search_type'], $params['search_keyword']);
            } else if (empty($params['search_keyword']) && !empty($params['search_type'])) {
                // 다중검색
                $query = $query->where(function ($query) use ($params) {
                    $query->where('user_id', $params['search_keyword']);
                    $query->orWhere('user_name', $params['search_keyword']);
                    $query->orWhere('nick_name', $params['search_keyword']);
                    $query->orWhere('phone', $params['search_keyword']);
                    $query->orWhere('email', $params['search_keyword']);
                });
            }

            if (isset($params['select'])) {
                $query = $query->select($params['select']);
            } else {
                $query = $query->select([
                    'bl_admin_users.*',
                    'code_user_type.code_name as user_type_kr'
                ]);
            }

            // 리턴 종류
            return $this->setReturnFormat($params, $paginate, $query);

        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
