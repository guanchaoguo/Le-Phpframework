<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;

class RoomGameType extends BaseModel
{
    use  Authenticatable;
    protected $table = 'room_game_type';
    public $timestamps = false; //关闭创建时间、更新时间
}
