<?php
/**
 * Created by PhpStorm.
 * User: chensongjian
 * Date: 2017/6/13
 * Time: 10:37
 * 用户在线统计模型
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatisOnlineUser extends Model
{
    protected $table = 'statis_online_user';
    public $timestamps = false; //关闭创建时间、更新时间
}