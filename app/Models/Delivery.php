<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/4/6
 * Time: 17:34
 */

namespace App\Models;
use Illuminate\Auth\Authenticatable;

class Delivery extends BaseModel
{
    use  Authenticatable;
    protected $table = 'game_platform_delivery';
    public $timestamps = false; //关闭创建时间、更新时间
}