<?php

namespace App\Http\Controllers\Game;

use Illuminate\Http\Request;
use App\Models\Player;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{



    /**
     * @api {post} /lebogame/user/token 玩家获取token认证
     * @apiDescription 玩家获取token认证
     * @apiGroup Auth
     * @apiPermission none
     * @apiParam {String} user_name 用户名 chensj
     * @apiHeader {String} Accept http头协议值为 'application/vnd.pt.lebogame.v2.2+json'
     * @apiVersion 2.2.0
     * @apiSuccessExample {json} 成功返回:
        {
        "code": 0,
        "text": "认证成功",
        "result": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9sZWJvZ2FtZS91c2VyL3Rva2VuIiwiaWF0IjoxNDk4NDQ2MjYxLCJleHAiOjE0OTg2NjIyNjEsIm5iZiI6MTQ5ODQ0NjI2MSwianRpIjoiOHpJMFU4N2lNTTVOMURiMSIsInN1YiI6OTI2MTMxfQ.5IozZ8tRltebGGQrhfhiQ1Srf2KWEJGWBjNqDw8o3o4"
        }
        }
     * @apiSuccessExample {json} 失败返回:
     *     {
     *       "code": 400,
     *        "text":'',
     *        "result":''
     *     }
     */
    public function login(Request $request)
    {

        $credentials = $request->only('user_name');
        $credentials['user_name'] = decrypt_($credentials['user_name']);

        // 验证失败返回403

        if (! $token = \Auth::guard($this->guard)->attempt($credentials)) {
            return $this->response->array([
                'code'=>403,
                'text'=>trans('auth.incorrect'),
                'result'=>'',
            ]);
        }

        //用户权限
        return $this->response->array([
            'code' => 0,
            'text' => trans('auth.success'),
            'result' => [
                'token' => $token,
            ]
        ]);
    }


    public function refreshToken()
    {

        $token = Auth::guard($this->guard)->refresh();
        return $this->response->array([
            'code'=>0,
            'text'=>trans('auth.refresh'),
            'result'=>compact('token'),
        ]);

    }

}
