<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class Agent extends BaseModel
{
    use  Authenticatable;
    protected $table = 'lb_agent_user';
    protected $hidden = ['password'];
//    public $timestamps = false; //关闭创建时间、更新时间
    const CREATED_AT = 'add_time';
    const UPDATED_AT = 'update_time';
    //代理商所选的游戏种类
    public function hallGames()
    {
        return $this->hasMany(AgentGame::class, 'agent_id', 'id')->select('game_id','hall_id','agent_game.status');
    }
}
