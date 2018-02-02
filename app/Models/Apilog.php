<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/4/10
 * Time: 15:22
 * 游戏接口日志控制器
 */

namespace App\Models;




use Jenssegers\Mongodb\Eloquent\Model;

class Apilog extends Model
{
    protected $table = 'api_log';
    protected $connection = 'mongodb';
    protected $hidden = ['_id'];
    public $timestamps = false; //关闭创建时间、更新时间
}