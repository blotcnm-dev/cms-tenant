<?php

namespace App\Models\Admins;

use App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class AdminPasswordChangeLog extends Model
{
    use HasFactory, ModelTrait, HasApiTokens;

    public $table = "bl_admin_password_change_logs";
    public $tableKoNm = "관리자 패스워드 로그";
    public $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];    //대량할당 해제
    protected $hidden = [
        'password',
    ];
}
