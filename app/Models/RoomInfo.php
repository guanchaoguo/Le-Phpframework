<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;

class RoomInfo extends BaseModel
{
    use  Authenticatable;
    protected $table = 'room_info';
    public $timestamps = false; //关闭创建时间、更新时间
}
