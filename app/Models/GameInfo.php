<?php

namespace App\Models;

/**
游戏模型
*/
use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class GameInfo extends BaseModel
{
    use  Authenticatable;
    protected $table = 'game_info';
    public $timestamps = false; //关闭创建时间、更新时间

    //游戏分类
    public function gameCat()
    {
        return $this->hasOne(GameCat::class, 'id', 'cat_id')->select('id','cat_name', 'game_cat_code');
    }
    //游戏厅
    public function gameHall()
    {
        return $this->belongsToMany(GameHall::class, 'hall_game_ra', 'game_id', 'hall_id')->select  ('id','game_hall_code');
    }
}
