<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkl.com
 * Date: 2017/2/14
 * Time: 13:07
 * 后台用户角色模型
 */
namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class RoleGroup extends BaseModel
{
    use Authenticatable;
    protected $table = 'role_group';
    public $timestamps = false; //关闭创建时间、更新时间

}