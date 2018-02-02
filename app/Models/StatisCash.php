<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/6/2
 * Time: 10:38
 */
namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class StatisCash extends Model
{
    use  Authenticatable;
    protected $table = 'statis_cash';
    public $timestamps = false; //关闭创建时间、更新时间
}