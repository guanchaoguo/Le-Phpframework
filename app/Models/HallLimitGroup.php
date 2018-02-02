<?php

namespace App\Models;

/**
厅限额组模型
*/
use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class HallLimitGroup extends BaseModel
{
    use  Authenticatable;
    protected $table = 'hall_limit_group';
    public $timestamps = false; //关闭创建时间、更新时间

    //限额分组明细
    public function limitItem()
    {
        return $this->hasMany(HallLimitItem::class, 'group_id', 'id')->select('game_cat_id','max_money','min_money','bet_area')->orderBy('game_cat_id')->orderBy('bet_area');
    }
}
