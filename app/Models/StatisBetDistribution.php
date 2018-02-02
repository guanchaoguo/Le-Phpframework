<?php
/**
 * Created by PhpStorm.
 * User: chensongjian
 * Date: 2017/6/14
 * Time: 14:17
 * 时段投注额分布模型
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatisBetDistribution extends Model
{
    protected $table = 'statis_bet_distribution';
    public $timestamps = false; //关闭创建时间、更新时间
}