<?php
/**
 * 游戏模板对应图片.
 * User: chengkang
 * Date: 2017/4/11
 * Time: 14:00
 */
namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class GameTemplateImages extends BaseModel
{
    use  Authenticatable;
    protected $table = 'game_template_images';
    public $timestamps = false; //关闭创建时间、更新时间
    
}