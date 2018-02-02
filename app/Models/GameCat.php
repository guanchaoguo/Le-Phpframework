<?php

namespace App\Models;

/**
游戏分类模型
*/
use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class GameCat extends BaseModel
{
    use  Authenticatable;
    protected $table = 'game_cat';
    public $timestamps = false; //关闭创建时间、更新时间

}
