<?php
/**
 * 文案活动
 * User: chensongjian
 * Date: 2017/4/17
 * Time: 13:21
 */

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class GamePlatformActivity extends BaseModel
{
    use  Authenticatable;
    protected $table = 'game_platform_activity';
    protected $hidden = [];
    const UPDATED_AT='update_date';
    const CREATED_AT = 'add_date';

    /**
     * 文案Banner所属厅主
     * @return mixed
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class, 'p_id', 'id')->select('user_name');
    }
}