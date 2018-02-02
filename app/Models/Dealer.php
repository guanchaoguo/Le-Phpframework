<?php
/**
 * 荷官数据模型
 * User: chensongjian
 * Date: 2017/10/13
 * Time: 11:28
 */

namespace App\Models;

use Illuminate\Auth\Authenticatable;

class Dealer extends BaseModel
{
    use  Authenticatable;
    protected $table = 'dealer_info';
    public $timestamps = false; //关闭创建时间、更新时间
}