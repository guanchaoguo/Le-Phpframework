<?php
/**
 * 游戏模板.
 * User: chengkang
 * Date: 2017/4/11
 * Time: 14:00
 */
namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class GameTemplate extends BaseModel
{
    use  Authenticatable;
    protected $table = 'game_template';
    public $timestamps = false; //关闭创建时间、更新时间

    public function images()
    {
        return $this->hasMany(GameTemplateImages::class, 't_id', 'id')->select('img',\DB::raw('CONCAT("'.env('APP_HOST').'", img) AS full_img'));
    }
}