<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkl.com
 * Date: 2017/2/15
 * Time: 10:38
 * 角色分组菜单权限模型
 */
namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class RoleGroupMenu extends BaseModel
{
    // 软删除和用户验证attempt
    use  Authenticatable;
    public $timestamps = false; //关闭创建时间、更新时间
    protected $table = 'role_group_menus';


}