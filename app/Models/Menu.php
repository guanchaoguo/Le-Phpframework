<?php

namespace App\Models;
/*菜单模型*/
use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class Menu extends BaseModel implements AuthenticatableContract, JWTSubject
{
    // 软删除和用户验证attempt
    use  Authenticatable;
    public $timestamps = false; //关闭创建时间、更新时间
    protected $table = 'menus';

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

/*    public function users()
    {
        return $this->belongsToMany(PlatformUser::class);
    }*/


}
