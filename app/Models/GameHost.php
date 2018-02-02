<?php
/**
 * Created by PhpStorm.
 * User: guanc
 * Date: 2017/11/21
 * Time: 16:19
 */
namespace App\Models;

/**
游戏厅模型
 */
use Illuminate\Auth\Authenticatable;
class GameHost extends BaseModel
{
    use  Authenticatable;
    const CREATED_AT = 'add_time';
    const UPDATED_AT = 'update_time';
    protected $table = 'game_host';
}