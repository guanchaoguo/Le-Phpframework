<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class AgentGame extends BaseModel
{
    use  Authenticatable;
    protected $table = 'agent_game';
    public $timestamps = false; //关闭创建时间、更新时间


}
