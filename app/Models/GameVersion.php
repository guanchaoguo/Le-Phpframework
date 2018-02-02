<?php
/**
 * 游戏版本更新模型
 * User: chensongjian
 * Date: 2017/6/16
 * Time: 11:25
 */

namespace App\Models;

class GameVersion extends BaseModel
{
    protected $table = 'game_version';
    public $timestamps = false; //关闭创建时间、更新时间
}