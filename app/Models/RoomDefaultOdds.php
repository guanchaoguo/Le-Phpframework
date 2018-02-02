<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;

class RoomDefaultOdds extends BaseModel
{
    use  Authenticatable;
    protected $table = 'room_default_odds';
    public $timestamps = false; //关闭创建时间、更新时间
}
