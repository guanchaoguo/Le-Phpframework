<?php

namespace App\Models;
/*平台用户表*/
use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class PlatformUser extends BaseModel implements AuthenticatableContract, JWTSubject
{
    // 软删除和用户验证attempt
    use  Authenticatable;
//    public $timestamps = false; //关闭创建时间、更新时间
    const CREATED_AT = 'add_time';//替换默认
    const UPDATED_AT = 'update_time';
    // 查询用户的时候，不暴露密码
    protected $hidden = ['password'];
    protected $table = 'lb_platform_user';

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
    //用户权限
    public function roles()
    {
        return $this->belongsToMany(Menu::class, 'user_menus', 'user_id', 'menu_id');
    }

    public function roleGroup() {
        return $this->belongsTo(RoleGroup::class, 'group_id','id')->select('group_name');
    }
}
