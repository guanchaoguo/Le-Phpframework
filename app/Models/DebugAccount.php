<?php
namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class DebugAccount extends Model
{
    protected $table = 'api_statistics_log';
    protected $connection = 'mongodb';
    protected $hidden = ['_id'];
    public $timestamps = false; //关闭创建时间、更新时间
}