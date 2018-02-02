<?php
/**
 * 包网费
 * Created by PhpStorm.
 * User: chensongjian
 * Date: 2017/3/31
 * Time: 10:08
 */

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class GameCost extends BaseModel
{
    use  Authenticatable;
    protected $table = 'game_platform_cost';
    protected $hidden = [];
    public $timestamps = false; //关闭创建时间、更新时间
}