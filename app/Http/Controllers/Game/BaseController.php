<?php

namespace App\Http\Controllers\Game;

use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    // 接口帮助调用
    use Helpers;
    //使用玩家的用户组认证
    protected $guard = 'player';
    //用户信息
    protected $userInfo ;
    public function __construct()
    {
        $this->userInfo = \Auth::guard($this->guard)->user();
    }
}
