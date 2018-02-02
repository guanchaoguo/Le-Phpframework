<?php

namespace App\Models;

/**
游戏厅模型
*/
use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class GameHall extends BaseModel
{
    use  Authenticatable;
    protected $table = 'game_hall';

    //游戏厅下的游戏
    public function games()
    {
        return $this->belongsToMany(GameInfo::class, 'hall_game_ra', 'hall_id', 'game_id')->where('game_info.status','<>', 2)->select  ('id','cat_id','game_name','game_code','sort_id','status');
    }
}
