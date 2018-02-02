<?php
/**
 * 取消派彩日志
 * User: chensongjian
 * Date: 2017/7/19
 * Time: 11:00
 */
namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
class ExceptionCashLog extends Eloquent
{
    use  Authenticatable;
    public $timestamps = false; //关闭创建时间、更新时间
    protected $connection = 'mongodb';
    protected $table = 'exception_cash_log';

}