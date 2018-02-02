<?php
/**
 * 厅主公告
 * User: chensongjian
 * Date: 2017/10/13
 * Time: 11:17
 */

namespace App\Models;

use Illuminate\Auth\Authenticatable;

class AgentMessage extends BaseModel
{
    use  Authenticatable;
    protected $table = 'hall_message';
    public $timestamps = false; //关闭创建时间、更新时间
}

