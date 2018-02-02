<?php

namespace App\Models;

/**
厅限额分组明细模型
*/
use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class HallLimitItem extends BaseModel
{
    use  Authenticatable;
    protected $table = 'hall_limit_item';
    public $timestamps = false; //关闭创建时间、更新时间

    public function gameInfo()
    {
        return $this->belongsTo(GameInfo::class, 'id', 'game_id');
    }
}
