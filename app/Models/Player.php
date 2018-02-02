<?php
/**
 * Created by PhpStorm.
 * User: chengkang
 * Date: 2017/2/5
 * Time: 18:00
 * Desc: 玩家模型
 */
namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class Player extends BaseModel implements AuthenticatableContract, JWTSubject
{
    use  Authenticatable;

    // jwt 需要实现的方法
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // jwt 需要实现的方法
    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $table = 'lb_user';
    protected $hidden = [];
    public $timestamps = false; //关闭创建时间、更新时间
    protected $primaryKey = 'uid';
}