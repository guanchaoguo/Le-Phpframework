<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;

class RoomRules extends BaseModel
{
    use  Authenticatable;
    protected $table = 'room_play_rules';
    public $timestamps = false; //关闭创建时间、更新时间
}
