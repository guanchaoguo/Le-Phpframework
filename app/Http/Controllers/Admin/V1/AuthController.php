<?php

namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
//use App\Jobs\SendRegisterEmail;
use Illuminate\Auth\AuthManager;
use App\Models\PlatformUser;
use App\Models\Menu;
//use App\Models\Language;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{

    protected $guard = 'admin';
    public function __construct()
    {
        \Auth::guard($this->guard);
    }

    /**
     * @api {post} /authorization 登录
     * @apiDescription 登录
     * @apiGroup Auth
     * @apiPermission none
     * @apiParam {String} user_name 用户名 chensj
     * @apiParam {String} password  密码 111111
     * @apiParam {String} gid  验证码GID
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiVersion 0.1.0
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
            "code": 0,
            "text": "认证成功",
            "result": {
            "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYXBwLWxvYy5kZXYvYXBpL2F1dGhvcml6YXRpb24iLCJpYXQiOjE0ODYyNTkyNzYsImV4cCI6MTQ4NjQ3NTI3NiwibmJmIjoxNDg2MjU5Mjc2LCJqdGkiOiJlMGJjOTI1ZTMxNDU1NDgxNWFmZGVhM2E1M2I5NTM0MSIsInN1YiI6MX0.H8S0KYxmlrY_D3XIuDmsyuu82mo1_TpGsjtbvXL77YM",
            "user": {
            "id": 1,
            "user_name": "chensj",
            "desc": "陈",
            "group_id": 1,
            "tel": "15013777164",
            "account_state": 1,
            "add_time": "2017-01-20 06:13:09",
            "update_time": "2017-01-20 07:56:39",
            "ip_info": "127.0.0.1"
            },
            "menus": [
            {
            "id": 1,
            "parent_id": 0,
            "title_cn": "账号管理",
            "desc": "账号管理",
            "link_url": "/account/list",
            "state": 1,
            "sort_id": 1,
            "menu_code": "account",
            "_child": [
            {
            "id": 5,
            "parent_id": 1,
            "title_cn": "厅主管理",
            "desc": "厅主管理",
            "link_url": "/haller/list",
            "state": 1,
            "sort_id": 1,
            "menu_code": "haller"
            },
            {
            "id": 6,
            "parent_id": 1,
            "title_cn": "代理管理管理",
            "desc": "代理",
            "link_url": "/agent/list",
            "state": 1,
            "sort_id": 1,
            "menu_code": "agent"
            },
            {
            "id": 7,
            "parent_id": 1,
            "title_cn": "玩家管理",
            "desc": "玩家管理",
            "link_url": "/player/list",
            "state": 1,
            "sort_id": 1,
            "menu_code": "palyer"
            }
            ]
            },
            {
            "id": 2,
            "parent_id": 0,
            "title_cn": "游戏管理",
            "desc": "游戏管理",
            "link_url": "/game/list",
            "state": 1,
            "sort_id": 1,
            "menu_code": "game",
            "_child": [
            {
            "id": 8,
            "parent_id": 2,
            "title_cn": "游戏列表",
            "desc": "游戏列表",
            "link_url": "/games/list",
            "state": 1,
            "sort_id": 1,
            "menu_code": "games"
            },
            {
            "id": 9,
            "parent_id": 2,
            "title_cn": "游戏限额",
            "desc": "游戏限额",
            "link_url": "games/limit",
            "state": 1,
            "sort_id": 1,
            "menu_code": "games_limit"
            }
            ]
            },
            {
            "id": 3,
            "parent_id": 0,
            "title_cn": "报表统计",
            "desc": "报表统计",
            "link_url": "/report/list",
            "state": 1,
            "sort_id": 1,
            "menu_code": "report",
            "_child": [
            {
            "id": 10,
            "parent_id": 3,
            "title_cn": "游戏数据统计",
            "desc": "游戏数据统计",
            "link_url": "game_report/list",
            "state": 1,
            "sort_id": 1,
            "menu_code": "game_report",
            "_child": [
            {
            "id": 11,
            "parent_id": 10,
            "title_cn": "查询游戏",
            "desc": "查询游戏",
            "link_url": "game_select/list",
            "state": 1,
            "sort_id": 1,
            "menu_code": "game_select"
            }
            ]
            }
            ]
            }
            ],
            "languages": {
            "简体中文": "zh-cn",
            "English": "en",
            "繁体中文": "zh-tw"
            },
            "timezones": {
            "(GMT+08:00) Urumqi": "Asia/Hong_Kong"
            }
            }
            }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 200
     *     {
     *       "code": 400,
     *        "text":'',
     *        "result":''
     *     }
     */
    public function login(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'user_name' => 'required',
            'password' => 'required',
            'captcha' => 'required|max:6',
            'gid'      => 'required|max:20'
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors(),
                'result'=>'',
            ]);
        }
        if($captcha = $request->input('captcha')){

            $redis = Redis::connection("default");

            if($redis->get($request->input('gid')) != $captcha){
                //验证码错误需要delete掉redis数据
                $redis->del($request->input('gid'));
                return $this->response->array([
                    'code'=>400,
                    'text'=>trans('auth.captcha'),
                    'result'=>'',
                ]);
            }
        }


        $where = [
            'user_name' => $request->input('user_name'),
//            'account_state' => 1,
        ];
        $info = PlatformUser::where($where)->first();
        if( !$info) {

            return $this->response->array([
                'code'=>403,
                'text'=>trans('auth.incorrect'),
                'result'=>'',
            ]);

        }

        if($info['account_state'] != 1) {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('auth203.frozen'),
                'result'=>'',
            ]);
        }
        if( ! $info['salt'] ) {

            PlatformUser::where($where)->update(['salt' =>randomkeys(20) ]);
        }

        $salt = PlatformUser::where($where)->pluck('salt')[0];

        $credentials = $request->only('user_name', 'password');
        $credentials['password'] .= $salt;
        $credentials['account_state'] = 1;
        // 验证失败返回403
        if (! $token = Auth::attempt($credentials)) {
            return $this->response->array([
                'code'=>403,
                'text'=>trans('auth.incorrect'),
                'result'=>'',
            ]);
        }

        //用户信息
        $user = PlatformUser::where(['user_name'=> $credentials['user_name'],'account_state'=>1])->first();
        //获取分组菜单数据
        $groupMenus = DB::table('role_group_menus')->where('role_id',$user->group_id)->select('menu_id','parent_id')->get();
        $roles = [];
        foreach ($groupMenus as $role) {
            $roles[] = $role->menu_id;
            $roles[] = $role->parent_id;
        }
        $roles = array_unique($roles);

        //获取用户菜单权限菜单栏
//        DB::raw('CONCAT("'.env('IMAGE_HOST').'", icon) AS icon')
        $menus = Menu::select('*')->orderBy('sort_id')->whereIn('id', $roles)->where('state',1)->get()->toArray();
        //权限菜单TODO
        $menus = list_to_tree($menus,'id','parent_id');
        //语言列表
//        $language = language::get()->toArray();
        //记录日志
        @addLog(['action_name'=>'登录','action_desc'=>' 进行账号登录 ','action_passivity'=>'登录']);


        PlatformUser::where(['user_name'=> $credentials['user_name'],'account_state'=>1])->update(['ip_info' => $request->ip()/*,'token' => $token*/]);
        //用户权限
        return $this->response->array([
            'code' => 0,
            'text' => trans('auth.success'),
            'result' => [
                'token' => $token,
                'user' => $user,
                'menus' => $menus,
                'languages' =>config('language'),
                'timezones' =>config('timezones'),
            ]
        ]);
    }

    /**
     * @api {post} /auth/token/refresh 刷新token(refresh token)
     * @apiDescription 刷新token(refresh token)
     * @apiGroup Auth
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiHeader {String} Authorization 用户旧的jwt-token, value已Bearer开头
     * @apiHeaderExample {json} Header-Example:
     *     {
     *       "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHA6XC9cL21vYmlsZS5kZWZhcmEuY29tXC9hdXRoXC90b2tlbiIsImlhdCI6IjE0NDU0MjY0MTAiLCJleHAiOiIxNDQ1NjQyNDIxIiwibmJmIjoiMTQ0NTQyNjQyMSIsImp0aSI6Ijk3OTRjMTljYTk1NTdkNDQyYzBiMzk0ZjI2N2QzMTMxIn0.9UPMTxo3_PudxTWldsf4ag0PHq1rK8yO9e5vqdwRZLY"
     *     }
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *       "code":0,
     *       "text":"刷新成功",
     *       "result":"9UPMTxo3_PudxTWldsf4ag0PHq1rK8yO9e5vqdwRZLY.eyJzdWIiOjEsImlzcyI6Imh0dHA6XC9cL21vYmlsZS5kZWZhcmEuY29tXC9hdXRoXC90b2tlbiIsImlhdCI6IjE0NDU0MjY0MTAiLCJleHAiOiIxNDQ1NjQyNDIxIiwibmJmIjoiMTQ0NTQyNjQyMSIsImp0aSI6Ijk3OTRjMTljYTk1NTdkNDQyYzBiMzk0ZjI2N2QzMTMxIn0.eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9"
     *     }
     */
    public function refreshToken()
    {

        $token = Auth::refresh();
        
//        return $this->response->array(compact('token'));
        return $this->response->array([
            'code'=>0,
            'text'=>trans('auth.refresh'),
            'result'=>compact('token'),
        ]);

    }

    /**
     * @api {post} /users 注册(register)
     * @apiDescription 注册(register)
     * @apiGroup Auth
     * @apiPermission none
     * @apiVersion 0.1.0
     * @apiParam {Email}  email   email[unique]
     * @apiParam {String} password   password
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         token: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHA6XC9cL21vYmlsZS5kZWZhcmEuY29tXC9hdXRoXC90b2tlbiIsImlhdCI6IjE0NDU0MjY0MTAiLCJleHAiOiIxNDQ1NjQyNDIxIiwibmJmIjoiMTQ0NTQyNjQyMSIsImp0aSI6Ijk3OTRjMTljYTk1NTdkNDQyYzBiMzk0ZjI2N2QzMTMxIn0.9UPMTxo3_PudxTWldsf4ag0PHq1rK8yO9e5vqdwRZLY
     *     }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *         "email": [
     *             "该邮箱已被他人注册"
     *         ],
     *     }
     */
    public function register(Request $request)
    {


        $validator = \Validator::make($request->input(), [
            'user_name' => 'required|unique:lb_platform_user',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code' => 400,
                'text'=> $validator->messages(),
                'result' => '',
            ]);
        }

        $name = $request->get('user_name');
        $password = $request->get('password');

        $attributes = [
            'user_name' => $name,
            'password' => app('hash')->make($password.randomkeys(20)),
        ];
        $user = PlatformUser::create($attributes);

        // 用户注册事件

        $token = \Auth::fromUser($user);

        // 用户注册成功后发送邮件
        // 或者 \Queue::push(new SendRegisterEmail($user));
        //dispatch(new SendRegisterEmail($user));

        return $this->response->array([
            'code' => 0,
            'text'=> trans('auth.register'),
            'result' => compact('token'),
        ]);
    }

    /**
     * @api {post} /language 语言列表
     * @apiDescription 语言列表
     * @apiGroup Auth
     * @apiPermission none
     * @apiVersion 0.1.0
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *      {
                "code": 0,
                "text": "操作成功",
                "result": {
                    "language": {
                        "简体中文": "zh-cn",
                        "English": "en",
                        "繁体中文": "zh-tw"
                    }
                }
            }
     */
    public function language()
    {
        return $this->response->array([
            'code' => 0,
            'text'=> trans('agent.success'),
            'result' => [
                'language' => config('language'),
            ],
        ]);
    }
}
