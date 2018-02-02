<?php
/**
 * 游戏分成
 * Created by PhpStorm.
 * User: chensongjian
 * Date: 2017/3/31
 * Time: 10:09
 */

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class GameScale extends BaseModel
{
    use  Authenticatable;
    protected $table = 'game_platform_scale';
    protected $hidden = [];
    public $timestamps = false; //关闭创建时间、更新时间
}