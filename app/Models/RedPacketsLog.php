<?php
namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class RedPacketsLog extends Eloquent
{
    use  Authenticatable;
    protected $table = 'packets_log';
    protected $connection = 'mongodb';
    protected $hidden = ['_id'];
    public $timestamps = false; //关闭创建时间、更新时间

}