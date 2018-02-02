<?php

namespace App\Http\Controllers\Admin\V1;

use App\Models\Player;
use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\AgentGame;
use App\Models\GameHall;
use App\Models\HallLimitGroup;
use App\Models\HallLimitItem;
use App\Models\GameCost;
use App\Models\GameScale;
use Illuminate\Support\Facades\DB;
use App\Models\AgentMenuList;
use App\Models\AgentMenu;
use App\Models\AgentMenus;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\File;

class AgentController extends BaseController
{
    public function __construct()
    {
//        $this->userRepository = $userRepository;
    }

    /**
     * @api {get} /agents/{grade_id} 厅主（代理商）列表
     * @apiDescription 获取厅主（代理商）列表 grade_id:代理级别，总代（厅主）则为1，2为二级代理
     * @apiGroup agent
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} user_name 用户名
     * @apiParam {String} tel 手机号码
     * @apiParam {Number} account_lock 是否锁定,1锁定,0不锁定
     * @apiParam {Number} start_add_time 开始时间 2017-04-20 16:11:25
     * @apiParam {Number} end_add_time 结束时间 2017-04-20 16:11:25
     * @apiParam {Number} page 当前页
     * @apiParam {Number} page_num 每页条数
     * @apiParam {Number} is_page 是否分页 1：是，0：否 ，默认1
     * @apiParam {Number} show_all 展示所有类型的代理商 1：是，0：否 默认为否
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
                "code": 0,
                "text": "ok",
                "result": {
                    "total": 2,
                    "per_page": 1,
                    "current_page": 1,
                    "last_page": 2,
                    "next_page_url": "http://app-loc.dev/api/agents/1?page=2",
                    "prev_page_url": null,
                    "from": 1,
                    "to": 1,
                    "data": [
                        {
                        "id": 1,
                        "user_name": "csj",
                        "real_name": "陈松坚",
                        "desc": "厅主",
                        "grade_id": 1,
                        "tel": "15013777164",
                        "account_state": 1,
                        "add_time": "2017-01-20 06:55:32",
                        "update_time": "2017-01-20 07:03:51",
                        "ip_info": "127.0.0.1",
                        "parent_id": 0,
                        "mapping": null,
                        "sub_count": 1,
                        "area": "中国",
                        "tel_pre": "86",
                        "email": "184444444@qq.com",
                        "account_lock": 0,
                        "lock_rank": null,
                        "charge_mode": 1,
                        "charge_fixed": 500,
                        "charge_percentage": 20,
                        "time_zone": "(GMT+08:00) Asia / Beijing",
                        "lang_code": "zh_cn",
                        "sub_user": 0,
                        "lock_reason": "",
                        "account_type": 1,//1为正式账号，2为测试账号
                        "agent_code": "csj"
                        "connect_mode": 0 //厅主对接方式，0为额度转换，1为共享钱包，默认为0
                        }
                    ]
                }
            }
     */
    public function index(Request $request,  $grade_id)
    {

        /*$ip = $request->ip();//获取IP地址
        var_dump($ip);die;
        $users = Admin::paginate(1)->toArray();
        var_dump($users);die;
       $re = $this->response->paginator($users, $adminTransformer);*/

        $user_name = $request->input('user_name');
        $tel = $request->input('tel');
        $account_lock = $request->input('account_lock');
        $page_num = $request->input('page_num',10);
        $is_page = $request->input('is_page',1);

        $start_add_time = $request->input('start_add_time');
        $end_add_time = $request->input('end_add_time');
        $show_all = $request->input('show_all', 0);//展示所有类型的代理商 1：是，0：否

        $db = Agent::select('id','user_name','real_name','sub_count','sub_user','add_time','account_lock','parent_id','account_type','connect_mode')->where('grade_id', (int)$grade_id);


        $db->where('account_state','<>', 3);
        //为厅主时，过滤厅主的子账号
        if($grade_id == 1) {
            $db->where('is_hall_sub', 0);
        } else {
            //过滤掉测试代理商
            !$show_all && $db->whereNotIn('account_type', [2, 3]);
        }
        if(isset($user_name) && !empty($user_name)) {
            $db->where('user_name', 'like', '%'.$user_name.'%');
        }

        if(isset($tel) && !empty($tel)) {
            $db->where('tel',$tel);
        }

        if(isset($account_lock) && $account_lock !== '') {
            $db->where('account_lock',$account_lock);
        }

        if(isset($start_add_time) && !empty($start_add_time)) {
            $db->where('add_time', '>=', $start_add_time);
        }
        if(isset($end_add_time) && !empty($end_add_time)) {
            $db->where('add_time', '<', $end_add_time);
        }

        $db->orderby('add_time','desc');
        if($is_page) {

            $agents = $db->paginate($page_num)->toArray();
        } else {

            $agents = [
                'data' =>$db->get()->toArray()
            ];
        }


        foreach ($agents['data'] as &$v){
            if($grade_id == 2) {
                $hall_name = Agent::where('id', $v['parent_id'])->pluck('user_name');
                $v['hall_name'] = isset($hall_name[0]) ? $hall_name[0] : '';
            }

            $v['sub_count'] = (int) $v['sub_count'];
            $v['sub_user'] = (int) $v['sub_user'];
        }
        unset($v);

        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => $agents,
        ]);

    }


    /**
     * @api {post} /agent/grade/{grade_id} 添加厅主代理(新)
     * @apiDescription 添加厅主代理 {grade_id}:代理级别 ，1：厅主，2：代理
     * @apiGroup agent
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token *
     * @apiParam {String} locale 语言 *
     * @apiParam {String} area 运营地区 *
     * @apiParam {String} time_zone 时区 *
     * @apiParam {String} user_name 登录名 *
     * @apiParam {String} real_name 昵称
     * @apiParam {String} email 邮箱 *
     * @apiParam {String} parent_name 上级代理名称（添加代理商时必须 *）
     * @apiParam {String} agent_code 代理商code，做为代理商玩家用户名前缀（添加代理商时必须 *）
     * @apiParam {String} tel_pre 手机国家代码
     * @apiParam {String} tel 手机号
     * @apiParam {String} password 密码 *
     * @apiParam {String} password_confirmation 确认密码 *
     * @apiParam {String} account_lock 是否锁定 1为永久锁定,0为未锁定
     * @apiParam {String} lock_reason 锁定原因
     * @apiParam {Int} connect_mode 厅主对接方式，0为额度转换，1为共享钱包，默认为0
     * @apiParam {Int} show_delivery 针对厅主，是否显示厅主交收统计  0：不显示，1：显示 ，默认1
     * @apiParam {Number} account_type 账号种类,1为正常账号,2为测试账号，3为联调账号，默认为1
     * @apiParam {json} games 游戏种类
        [
        "91-0-0",
        "93-0-0",
        "94-0-0",
        "95-0-0"
        ]
     * @apiParam {array} menus 菜单数据格式 [id-parent_id]
        [
        "91-0",
        "93-0",
        ]
     * @apiParam {Number} t_id 风格模板id
     * @apiParam {String} gameScale 游戏分成
        [
            {
            "start_profit": "0.00",//毛利润开始值
            "end_profit": "100.00",//毛利润结束值
            "scale": "30"//站成比例，单位：%
            }
        ]
     * @apiParam {String} gameCost 游戏费用
        {
            "roundot": "30000.00",//包网费
            "line_map": "30000.00",//线路图
            "upkeep": "30000.00",//维护费用
            "ladle_bottom": "30000.00"//包底
        }
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
            {
            "code": 0,
            "text": "保存成功",
            "result": ""
            }
     * @apiSuccessExample {json} games 游戏种类 数据格式
        [
        "91-0-0",
        "93-0-0",
        "94-0-0",
        "95-0-0"
        ]
     * @apiSuccessExample {json} menus 菜单权限 数据格式
        [
        "91-0",//id-parent_id
        "93-0"
        ]
     * @apiSuccessExample {json} gameScale 游戏分成 数据格式
        ps：需要转成string
        [
        {
        "start_profit": "0.00",//毛利润开始值
        "end_profit": "100.00",//毛利润结束值
        "scale": "30"//站成比例，单位：%
        }
        ]
     * @apiSuccessExample {json} gameCost 游戏费用 数据格式
        ps：需要转成string
        {
        "roundot": "30000.00",//包网费
        "line_map": "30000.00",//线路图
        "upkeep": "30000.00",//维护费用
        "ladle_bottom": "30000.00"//包底
        }
     * @apiErrorExample  {json} Error-Response:
     *     HTTP/1.1 200 OK
     *     {
            "code": 0,
            "text": "保存失败",
            "result": ""
            }
     */
    public function store2(Request $request, int $grade_id) {

        $attributes = ['grade_id' => $grade_id];
        //-------------基本信息start-----------------

        $attributes['area'] = $request->input('area');
        $attributes['time_zone'] = $request->input('time_zone');
        $attributes['user_name'] = $request->input('user_name');
        $attributes['password'] = $request->input('password');
        $attributes['email'] = $request->input('email');
        $attributes['real_name'] = $request->input('real_name');
        $attributes['tel_pre'] = $request->input('tel_pre');
        $attributes['tel'] = (string)$request->input('tel');
        $attributes['account_lock'] = (int) $request->input('account_lock');
        $attributes['lock_reason'] = $request->input('lock_reason');
        $attributes['add_time'] = date('Y-m-d H:i:s', time());
        $attributes['account_type'] = $request->input('account_type',1);//账号种类，1为正常账号,2为测试账号，默认为1
        $attributes['notify_url'] = $request->input('notify_url');//玩家离线通知地址
        $attributes['connect_mode'] = $request->input('connect_mode',0);//厅主接入方式
        $attributes['show_delivery'] = $request->input('show_delivery',1);//是否显示厅主交收统计
        //---------------基本信息end----------------

        $message = [
            'area.required' => trans('agent.area.required'),
            'time_zone.required' => trans('agent.time_zone.required'),
            'user_name.required' => trans('agent.agent_name.required'),
            'user_name.unique' => trans('agent.agent_name.unique'),
            'user_name.regex' => trans('agent.agent_name.regex'),
            'real_name.required' => trans('agent.real_name.required'),
            'real_name.regex' => trans('agent.real_name.regex'),
            'password.required' => trans('agent.password.required'),
            'password.min' => trans('agent.password.min'),
            'password.confirmed' => trans('agent.password.confirmed'),
            'email.required' => trans('agent.email.required'),
            'email.email' => trans('agent.email.email'),
            'email.unique' => trans('agent.email.unique'),
            'tel.required' => trans('agent.tel.required'),
        ];
        $validator = \Validator::make($request->input(), [
            'user_name' => [
                'required',
                'unique:lb_agent_user',
                'regex:/^[a-zA-z][a-zA-Z0-9_]{5,19}$/'
            ],
            'real_name' => [
                'required',
                'regex:/^[\w\_\x{4e00}-\x{9fa5}]{3,20}$/u'//中文、英文、数字、下划线结合而且3-20字符
            ],
            'password' => 'required|min:6|confirmed',
            'tel' => 'required',
            'email' => 'required|email|unique:lb_agent_user',
            'area' => 'required',
            'time_zone' => 'required',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $attributes['ip_info'] = $request->ip();
        $attributes['salt'] = randomkeys(20);
        $attributes['password'] = app('hash')->make($attributes['password'].$attributes['salt']);

        switch ($grade_id) {
            //厅主
            case 1:
                $attributes['t_id'] = (int) $request->input('t_id');//模板id
                break;
            //代理
            case 2:
                $parent_name =  $request->input('parent_name');//添加代理时必须
                if( ! $parent_name ) {
                    return $this->response->array([
                        'code'=>400,
                        'text'=> trans('agent.hall_id.required'),
                        'result'=>'',
                    ]);
                }

                $agent = Agent::where(['user_name' => $parent_name])->first();
                $attributes['parent_id'] = $agent->id;

                //代理商code
                $agent_code =  $request->input('agent_code');//添加代理时必须
                if( ! $agent_code ) {
                    return $this->response->array([
                        'code'=>400,
                        'text'=> trans('agent.agent_code.required'),
                        'result'=>'',
                    ]);
                }

                if( ! preg_match('/^[a-zA-z][a-zA-Z0-9_]{2,5}$/', $agent_code) ) {
                    return $this->response->array([
                        'code'=>400,
                        'text'=> trans('agent.agent_code.error'),
                        'result'=>'',
                    ]);
                }
                if( Agent::where(['agent_code' => $agent_code])->first() ) {
                    return $this->response->array([
                        'code'=>400,
                        'text'=> trans('agent.agent_code.unique'),
                        'result'=>'',
                    ]);
                }
                $attributes['agent_code'] = $agent_code;
                break;
            //类型错误返回
            default:
                return $this->response->array([
                    'code'=>400,
                    'text'=> trans('agent.param_error'),
                    'result'=>'',
                ]);
                break;
        }
        DB::beginTransaction();

        $user = Agent::create($attributes);
        if( ! $user ) {

            DB::rollBack();
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.add_fails'),
                'result' => '',
            ]);

        }




        //更改代理商数
        if($user->parent_id) {
            Agent::where('id',$user->parent_id)->increment('sub_count');
        }

        //厅主
        if( $grade_id == 1 ) {

            //其下级代理进行锁定或解锁
            $this->lockAgent($attributes['account_lock'], $user->id);
            //其下子账户进行锁定或解锁
            $this->lockSubAccount($attributes['account_lock'], $user->id);

            //开通游戏种类
            $hallGame = self::openHallGame($request->input('games'), $user->id);
            if($hallGame['code'] != 1) {
                DB::rollBack();
                return $this->response->array($hallGame['data']);
            }

            // 添加厅主缓存
            self::setHallGame($user->id);

            //游戏分成设置
            $gameScale = json_decode($request->input('gameScale'), true);
            $re_scale = self::setGameScale($gameScale, $user->id);

            if($re_scale['code'] != 1) {
                DB::rollBack();
                return $this->response->array($re_scale['data']);
            }
            //游戏费用设置
            $gameCost = json_decode($request->input('gameCost'), true);
            $re_cost = self::setGameCost($gameCost, $user->id);
            if($re_cost['code'] != 1) {
                DB::rollBack();
                return $this->response->array($re_cost['data']);
            }
            //为厅主添加默认限额
            self::addHallGameLimite($user->id);

            //为厅主添加默认监控规则
            $res_monitor_rule = self::addHallMonitorRule($user->id);
            if( $res_monitor_rule['code'] != 1) {
                DB::rollBack();
                return $this->response->array($res_monitor_rule['data']);
            }
        }
        //开通菜单权限
        $openMenuRole = self::openMenuRole($request->input('menus'), $user->id);

        if($openMenuRole['code'] != 1) {
            DB::rollBack();
            return $this->response->array($openMenuRole['data']);
        }

        //其下级玩家进行锁定或解锁
        $status = $attributes['account_lock'] == 0 ? 1 : 3;//玩家状态：1启用，3禁用
        $this->lockUser($status, $user->id, $grade_id);

        $agent_name = $grade_id == 1 ? '厅主' : '代理商';

        @addLog(['action_name'=>'添加'.$agent_name,'action_desc'=>' 添加了一个新'.$agent_name.'，账号为：'.$attributes['user_name'],'action_passivity'=>$attributes['user_name']]);
        DB::commit();
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>['user_name'=>$attributes['user_name'],'password'=>$request->input('password'),'account_type'=>$attributes['account_type']],
        ]);

    }

    /**
     * 为厅主添加默认监控规则
     * @param int $hall_id 厅主id
     */
    private function addHallMonitorRule(int $hall_id)
    {
        $t1 = 'sys_monitor';
        $t2 = 'sys_monitor_rule';
        $info = DB::table($t1)->where('hall_id', $hall_id)->first();
        if( $info ) {
            return [
                'code' => 1
            ];
        }

        $de_data = DB::table($t1)->where('hall_id', 0)->get()->toArray();
        if( ! $de_data ) {
           return [
                'code' => -1,
                'data' => [
                    'code' => 400,
                    'text' => trans('monitor_rule.monitor_data'),
                    'result' => '',
                ]
            ];
        }
        foreach ($de_data as &$item) {
            $item = (array)$item;
            $item['hall_id'] = $hall_id;
            unset($item['id']);
        }
        unset($item);

        $res = DB::table($t1)->insert($de_data);
        if( $res ) {
            $info = DB::table($t2)->where('hall_id', $hall_id)->first();
            if( $info ) {
                return [
                    'code' => 1
                ];
            }

            $de_data2 = DB::table($t2)->where('hall_id', 0)->get()->toArray();
            if( ! $de_data2 ) {
               return [
                    'code' => -1,
                    'data' => [
                        'code' => 400,
                        'text' => trans('monitor_rule.monitor_rule_data'),
                        'result' => '',
                    ]
                ];
            }
            $now_time = date('Y-m-d H:i:s');
            foreach ($de_data2 as &$item) {
                $item = (array)$item;
                $item['hall_id'] = $hall_id;
                $item['last_date'] = $now_time;
                unset($item['id']);
            }
            unset($item);

            $res = DB::table($t2)->insert($de_data2);

            //缓存监控规则
            self::monitorToRedis($hall_id);
            
            if( $res ) {
                return [
                    'code' => 1
                ];
            }
            return [
                'code' => -1,
                'data' => [
                    'code' => 400,
                    'text' => trans('monitor_rule.add_monitor_rule_fail'),
                    'result' => '',
                ]
            ];
        }
        return [
            'code' => -1,
            'data' => [
                'code' => 400,
                'text' => trans('monitor_rule.add_monitor_fail'),
                'result' => '',
            ]
        ];

    }

    /**
     *  同步监控默认规则到redis
     */
    private function monitorToRedis($hall_id)
    {
        $hallRuleList = DB::table("sys_monitor_rule")->where(["hall_id"=>$hall_id])->get()->toArray();
        $monitorList = DB::table("sys_monitor")->where(["hall_id"=>$hall_id])->get()->toArray();
//        $M001List = DB::table("sys_monitor")->where(["tag"=>"M001","hall_id"=>$hall_id])->get()->toArray();

        foreach ($monitorList as $k=>$v)
        {
            $hashData = [];
            foreach ($hallRuleList as $k2=>$v2)
            {
                if($v->tag == $v2->tag)
                {
                    $hashData[$v2->keycode] = $v2->value;
                }
            }
            $hashData['status'] = $v->status;

            $redis = Redis::connection("monitor");
            $res = $redis->hMset(env('MONITOR_RULE').":".$v->tag.":$hall_id",$hashData);
        }

    }

    /**
     * 添加厅主时，为厅主添加游戏默认限额（平台的默认限额）
     * @param int $hall_id 厅主id
     * @return int
     */
    private function addHallGameLimite(int $hall_id){
        //获取所有厅主
        $where = [
            'grade_id' => 1,
            'is_hall_sub' => 0,
            'parent_id' => 0,
            'id' => $hall_id,
        ];
        $halls = Agent::select('id')->where($where)->first();

        if( ! $halls ) {
            return -1;
        }

        $time = date('Y-m-d H:i:s',time());
        foreach ([0,1,2,3] as $cat) {
            foreach (['defaultA','defaultB','defaultC'] AS $default) {
                $data_insert1 = $data_insert2 = [
                    'agent_id' => $halls['id'],
                    'hall_type' => $cat,
                    'title' => $default,
                ];
                $re = HallLimitGroup::where($data_insert1)->first();
                if( ! $re ) {
                    $data_insert1['status'] = 1;
                    $data_insert1['uptime'] = $time;
                    $res = HallLimitGroup::create($data_insert1);

                    if( $res ) {
                        $data_insert2['agent_id'] = 0;
                        $limitItem = HallLimitGroup::where($data_insert2)->first()->limitItem->toArray();
                        foreach ($limitItem as &$item) {
                            $item['group_id'] = $res['id'];
                        }
                        unset($item);
                        $limitItem = StringShiftToInt($limitItem,['group_id','game_cat_id','max_money','min_money','bet_area']);
                        HallLimitItem::insert($limitItem);
                    }
                }
            }
        }
        return 1;

    }
    /**
     * 厅主锁定其下级代理
     * @param int $status 0 不锁定，1锁定
     * @param int $agent_id 厅主id
     * @return mixed
     */
    private function lockAgent( int $status, int $agent_id)
    {
        $reason = $status == 1 ? '所属厅主被锁定' : '';
        $where = ['grade_id' => 2, 'is_hall_sub' => 0,'parent_id' => $agent_id];
        return Agent::where($where)->update(['account_lock' => $status,'lock_reason' => $reason]);
    }

    /**锁定其下子账户（针对厅主）
     * @param int $status 0不锁定，1锁定
     * @param int $agent_id 厅主id
     * @return mixed
     */
    private function lockSubAccount(int $status, int $agent_id)
    {
        $reason = $status == 1 ? '所属厅主被锁定' : '';
        $where = ['grade_id' => 1, 'is_hall_sub' => 1,'parent_id' => $agent_id];
        return Agent::where($where)->update(['account_lock' => $status,'lock_reason' => $reason]);
    }

    /**锁定其下级玩家
     * @param int $status 1启用 3禁用
     * @param int $agent_id 代理id
     * @param int $grade_id 代理类型 1厅主， 2代理
     * @return bool
     */
    private function lockUser( int $status, int $agent_id, int $grade_id)
    {
        $where = [];

        switch ($grade_id) {
            case 1:
                $where['hall_id'] = $agent_id;
                break;
            case 2:
                $where['agent_id'] = $agent_id;
                break;
            default:
                return false;
        }

        return Player::where($where)->update(['account_state' => $status]);
    }


    /**
     * @api {put} /agent_/{agent_id}/grade/{grade_id} 编辑厅主代理(新)
     * @apiDescription 编辑厅主代理 {agent_id}:代理id,{grade_id}:代理级别 ，1：厅主，2：代理
     * @apiGroup agent
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} area 运营地区 *
     * @apiParam {String} time_zone 时区 *
     * @apiParam {String} real_name 昵称
     * @apiParam {Int} connect_mode 厅主对接方式，0为额度转换，1为共享钱包，默认为0
     * @apiParam {Int} show_delivery 针对厅主，是否显示厅主交收统计  0：不显示，1：显示 ，默认1
     * @apiParam {json} games 游戏种类
        [
        "91-0-0",//格式：'游戏id-厅id-是否显示'
        "93-0-0",
        "94-0-0",
        "95-0-0"
        ]
     * @apiParam {array} menus 菜单数据格式 [id-parent_id]
        [
        "91-0",
        "93-0",
        ]
     * @apiParam {Number} t_id 风格模板id
     * @apiParam {String} gameScale 游戏分成
        [
        {
        "start_profit": "0.00",//毛利润开始值
        "end_profit": "100.00",//毛利润结束值
        "scale": "30"//站成比例，单位：%
        }
        ]
     * @apiParam {String} gameCost 游戏费用
        {
        "roundot": "30000.00",//包网费
        "line_map": "30000.00",//线路图
        "upkeep": "30000.00",//维护费用
        "ladle_bottom": "30000.00"//包底
        }
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
        {
        "code": 0,
        "text": "保存成功",
        "result": ""
        }
     * @apiSuccessExample {json} games 游戏种类 数据格式
        [
        "91-0-0",
        "93-0-0",
        "94-0-0",
        "95-0-0"
        ]
     * @apiSuccessExample {json} menus 菜单权限 数据格式
        [
        "91-0",//id-parent_id
        "93-0"
        ]
     * @apiSuccessExample {json} gameScale 游戏分成 数据格式
        ps：需要转成string
        [
        {
        "start_profit": "0.00",//毛利润开始值
        "end_profit": "100.00",//毛利润结束值
        "scale": "30"//站成比例，单位：%
        }
        ]
     * @apiSuccessExample {json} gameCost 游戏费用 数据格式
        ps：需要转成string
        {
        "roundot": "30000.00",//包网费
        "line_map": "30000.00",//线路图
        "upkeep": "30000.00",//维护费用
        "ladle_bottom": "30000.00"//包底
        }
     * @apiSuccessExample {json} 返回4001 扣费模式切换时玩家余额不为零格式
        {
        "code": 4001,
        "text": "agent.balance_status",
        "result": {
        "num": 12007,//玩家数量
        "filename": "excel/n1api005_userInfo.csv",//文件名
        "url": "http://platform.dev/excel/n1api005_userInfo.csv"//玩家余额不为零csv下载地址
        }
        }
     * @apiErrorExample  {json} Error-Response:
     *     HTTP/1.1 200 OK
     *     {
        "code": 400,
        "text": "保存失败",
        "result": ""
        }
     */
    public function update(Request $request, int $grade_id, int $agent_id) {

        $attributes = [];
        //-------------基本信息start-----------------

        $attributes['area'] = $request->input('area');
        $attributes['time_zone'] = $request->input('time_zone');
//        $attributes['email'] = $request->input('email');
        $attributes['real_name'] = $request->input('real_name');
//        $attributes['tel_pre'] = $request->input('tel_pre');
//        $attributes['tel'] = (string)$request->input('tel');
//        $attributes['account_lock'] = (int) $request->input('account_lock');
//        $attributes['lock_reason'] = $request->input('lock_reason');
        $attributes['update_time'] = date('Y-m-d H:i:s', time());
        $attributes['connect_mode'] = $request->input('connect_mode',0);//厅主接入方式
        $attributes['show_delivery'] = $request->input('show_delivery',1);//是否显示厅主交收统计  0：不显示，1：显示
        //---------------基本信息end----------------
        $where = ['grade_id' => $grade_id, 'id' => $agent_id];
        if( ! $user = Agent::where($where)->first() ) {

            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.agent_not_exist'),
                'result' => '',
            ]);

        }
        $message = [
            'area.required' => trans('agent.area.required'),
            'tel.required' => trans('agent.tel.required'),
            'time_zone.required' => trans('agent.time_zone.required'),
            'email.required' => trans('agent.email.required'),
            'email.email' => trans('agent.email.email'),
            'email.unique' => trans('agent.email.unique'),
            'real_name.required' => trans('agent.real_name.required'),
            'real_name.regex' => trans('agent.real_name.regex'),
        ];
        $validator = \Validator::make($request->input(), [
//            'tel' => 'required',
//            'email' => 'required|email|unique:lb_agent_user,email,'.$agent_id,
            'real_name' => [
                'required',
                'regex:/^[\w\_\x{4e00}-\x{9fa5}]{3,20}$/u'//中文、英文、数字、下划线结合而且3-20字符
            ],
            'area' => 'required',
            'time_zone' => 'required',

        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        switch ($grade_id) {
            //厅主
            case 1:
                $attributes['t_id'] = (int) $request->input('t_id');//模板id

                if($attributes['connect_mode'] !== $user['connect_mode']) {

                    //模式不相等，判断厅主下面玩家余额是否都为0
                    $num = Player::select(['hall_name','agent_name','user_name','username_md','money'])->where('hall_id',$agent_id)->where('money', '<>', 0)->count();

                    if( $num ) {

                        $filename = 'excel/' . $user['user_name'] . '_userInfo.csv';
                        self::getHallUser($agent_id, $num, $filename);
                        return $this->response->array([
                            'code' => 4001,
                            'text' => trans('agent.balance_status'),
                            'result' => [
                                'num' => $num,
                                'filename' => $filename,
                                'url' => env('APP_HOST') . $filename,
                            ]
                        ]);
                    }
                }
                break;
            //代理
            case 2:
                /*$attributes['parent_id'] = (int) $request->input('parent_id');//添加代理时必须
                if( ! $attributes['parent_id'] ) {
                    return $this->response->array([
                        'code'=>400,
                        'text'=> trans('agent.hall_id.required'),
                        'result'=>'',
                    ]);
                }*/
                break;
            //类型错误返回
            default:
                return $this->response->array([
                    'code'=>400,
                    'text'=> trans('agent.param_error'),
                    'result'=>'',
                ]);
                break;
        }

        DB::beginTransaction();
        $re = Agent::where($where)->update($attributes);
        if( $re === false  ) {

            DB::rollBack();
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.add_fails'),
                'result' => '',
            ]);

        }

        //更改代理商数
        /*if($grade_id == 2 && $attributes['parent_id'] != $user->parent_id) {
            Agent::where('id',$user->parent_id)->decrement('sub_count');
            Agent::where('id',$attributes['parent_id'])->increment('sub_count');
        }*/

        if( $grade_id == 1 ) {

            /*//其下级代理进行锁定或解锁
            $this->lockAgent($attributes['account_lock'], $agent_id);
            //其下子账户进行锁定或解锁
            $this->lockSubAccount($attributes['account_lock'], $agent_id);*/

            //开通游戏种类
            $hallGame = self::openHallGame($request->input('games'), $agent_id);
            if($hallGame['code'] != 1) {
                DB::rollBack();
                return $this->response->array($hallGame['data']);
            }

            // 添加厅主缓存
            self::setHallGame($agent_id);

            //更新厅主下面的代理商白名单缓存（编辑有涉及到厅主扣费模式修改，需要更新缓存）
            WhitelistController::storeAgentWhitelist($agent_id);
            //游戏分成设置
            /*$gameScale = $request->input('gameScale');
            if( $gameScale ) {
                $gameScale = json_decode($gameScale, true);
                $re_scale = self::setGameScale($gameScale, $agent_id);
                if($re_scale['code'] != 1) {
                    DB::rollBack();
                    return $this->response->array($re_scale['data']);
                }
            }*/

            //游戏费用设置
            /*$gameCost = $request->input('gameCost');
            if( $gameCost ) {
                $gameCost = json_decode($gameCost, true);
                $re_cost = self::setGameCost($gameCost, $agent_id);
                if($re_cost['code'] != 1) {
                    DB::rollBack();
                    return $this->response->array($re_cost['data']);
                }
            }*/


        }

        //开通菜单权限
        /*$openMenuRole = self::openMenuRole($request->input('menus'), $agent_id);
        if($openMenuRole['code'] != 1) {
            DB::rollBack();
            return $this->response->array($hallGame['data']);
        }*/

        //其下级玩家进行锁定或解锁
        /*$status = $attributes['account_lock'] == 0 ? 1 : 3;//玩家状态：1启用，3禁用
        $this->lockUser($status, $agent_id, $grade_id);*/
        $agent_name = $grade_id == 1 ? '厅主' : '代理商';
        @addLog(['action_name'=>'编辑'.$agent_name,'action_desc'=>' 对'.$agent_name.' '.$request->input('user_name').' 进行编辑','action_passivity'=>$request->input('user_name')]);
        DB::commit();
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>'',
        ]);

    }

    //当扣费模式改变时，检测厅主下面玩家是否有余额
    /**
     * @api {get} /agent_/connectMode/ 检测厅主下面玩家是否有余额
     * @apiDescription 当扣费模式改变时，检测厅主下面玩家是否有余额
     * @apiGroup agent
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} hall_id 厅主id
     * @apiParam {String} connect_mode 扣费模式 0:额度转换，1:共享钱包
     * @apiSuccessExample {json} 成功返回格式:
        {
        "code": 0,
        "text": "保存成功",
        "result": ""
        }
     * @apiErrorExample  {json} 400错误返回格式:
        {
        "code": 400,
        "text": "",
        "result": ""
        }
     * @apiErrorExample  {json} 4001错误 检测到厅主下面玩家有余额:
        {
        "code": 4001,
        "text": "",
        "result": {
        "num": 12007,//会员数
        "filename": "excel/n1api005_userInfo.csv",//文件名
        "url": "http://platform.dev/excel/n1api005_userInfo.csv"//会员信息csv文件下载地址
        }
        }
     */
    public function checkHallAgentConnectMode(Request $request) {
        $hall_id = $request->input('hall_id');
        $connect_mode = $request->input('connect_mode');

        if($connect_mode !== '0' && $connect_mode !== '1') {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.param_error'),
                'result'=>'',
            ]);
        }
        $hallAgent = Agent::select('user_name','connect_mode')->where(['id' => $hall_id, 'grade_id' => 1,'is_hall_sub' => 0])->first();
        if( ! $hallAgent ) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.hall_not_exist'),
                'result'=>'',
            ]);
        }

        if($connect_mode !== $hallAgent['connect_mode']) {

            //模式不相等，判断厅主下面玩家余额是否都为0
            $num = Player::select(['hall_name','agent_name','user_name','username_md','money'])->where('hall_id',$hall_id)->where('money', '<>', 0)->count();

            if( $num ) {
                $dir = 'csv';
                $filename = $hallAgent['user_name'] . '_userInfo.csv';
                self::getHallUser($hall_id, $num, $dir, $filename);
                return $this->response->array([
                    'code' => 4001,
                    'text' => trans('agent.balance_status'),
                    'result' => [
                        'num' => $num,
                        'filename' => $filename,
                        'url' => env('APP_HOST') . $dir .'/'. $filename,
                    ]
                ]);
            }
        }

        return $this->response->array([
            'code'=> 0,
            'text'=> trans('agent.success'),
            'result'=>'',
        ]);

    }

    //处理厅主玩家余额不为零，导出数据
    private function getHallUser($hall_id, $total ,$dir, $filename){

        $pre_count = 1000;//每次取出条数
        set_time_limit(0);
        ini_set('memory_limit','500M');
        $title = [
                    'hall_name',
                    'agent_name',
                    'user_name',
                    'money'
        ];
        $save_path = $dir. '/' .$filename;
        if ( File::exists($save_path) ){
            File::delete($save_path);
        } else {
           if ( ! File::isDirectory($dir)) {
               File::makeDirectory($dir,  $mode = 0777, $recursive = false);
           }

        }
        // 打开PHP文件句柄
        $fp = fopen($save_path, 'a');
        // 将标题名称通过fputcsv写到文件句柄
        fputcsv($fp, $title);

        for($i = 0; $i <= intval($total / $pre_count); $i++) {
            $offset = $i * $pre_count;
            $user = Player::select(['hall_name','agent_name','username_md','money'])->where('hall_id',$hall_id)->where('money', '<>', 0)->offset($offset)->limit($pre_count)->get()->toArray();
            foreach ($user as &$item) {
                $item['username_md'] = encrypt_($item['username_md']);
                $rows = [];
                foreach ( $item as $v){
                    $rows[] = $v;
                }

                fputcsv($fp, $rows);
            }
            unset($item);
            ob_flush();
            flush();
        }

    }

    /**
     * @api {patch} /agent/{agent_id}/grade/{grade_id}/emailTel 修改手机&邮箱
     * @apiDescription 修改手机&邮箱 {agent_id}:代理id,{grade_id}:代理级别 ，1：厅主，2：代理
     * @apiGroup agent
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} email 邮箱 *
     * @apiParam {String} tel_pre 手机国家代码
     * @apiParam {String} tel 手机号 *
     * @apiSuccessExample {json} 成功返回:
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function setEmailTel(Request $request, int $grade_id, int $agent_id)
    {
        $attributes['email'] = $request->input('email');
        $attributes['tel_pre'] = $request->input('tel_pre');
        $attributes['tel'] = (string)$request->input('tel');

        $where = ['grade_id' => $grade_id, 'id' => $agent_id];
        if( ! $user = Agent::where($where)->first() ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.agent_not_exist'),
                'result' => '',
            ]);
        }
        $message = [
            'tel.required' => trans('agent.tel.required'),
            'email.required' => trans('agent.email.required'),
            'email.email' => trans('agent.email.email'),
            'email.unique' => trans('agent.email.unique'),
        ];
        $validator = \Validator::make($request->input(), [
            'tel' => 'required',
            'email' => 'required|email|unique:lb_agent_user,email,'.$agent_id,

        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $re = Agent::where($where)->update($attributes);
        if( $re !== false) {
            $agent_name = $grade_id == 1 ? '厅主' : '代理商';
            @addLog(['action_name'=>'修改'.$agent_name.'的邮箱、手机号','action_desc'=>' 对 '.$agent_name.$user['user_name'].'的邮箱、手机号进行修改','action_passivity'=> $user['user_name']]);
            return $this->response->array([
                'code'=>0,
                'text'=> trans('agent.success'),
                'result'=>'',
            ]);

        }

        return $this->response->array([
            'code' => 400,
            'text' => trans('agent.save_fails'),
            'result' => '',
        ]);

    }

    /**
     * @api {patch} /agent/{agent_id}/grade/{grade_id}/locking 修改锁定状态&原因
     * @apiDescription 修改锁定状态&原因 {agent_id}:代理id,{grade_id}:代理级别 ，1：厅主，2：代理
     * @apiGroup agent
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} account_lock 锁定 1：锁定 0不锁定*
     * @apiParam {String} lock_reason 锁定原因
     * @apiSuccessExample {json} 成功返回:
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function setLock(Request $request, int $grade_id, int $agent_id)
    {
        $attributes['account_lock'] = $request->input('account_lock', 0);
        $attributes['lock_reason'] = $request->input('lock_reason');

        $where = ['grade_id' => $grade_id, 'id' => $agent_id];
        if( ! $user = Agent::where($where)->first() ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.agent_not_exist'),
                'result' => '',
            ]);
        }

        $re = Agent::where($where)->update($attributes);
        if( $re !== false) {

            if( $grade_id == 1) {
                //其下级代理进行锁定或解锁
                $this->lockAgent($attributes['account_lock'], $agent_id);
                //其下子账户进行锁定或解锁
                $this->lockSubAccount($attributes['account_lock'], $agent_id);
                //更新厅主下面的代理商白名单缓存
                WhitelistController::storeAgentWhitelist($agent_id);
            } else {
                //更新代理商白名单缓存
                WhitelistController::storeAgentWhitelist($user['parent_id'], $agent_id);
            }

            //其下级玩家进行锁定或解锁
            $status = $attributes['account_lock'] == 0 ? 1 : 3;//玩家状态：1启用，3禁用
            $this->lockUser($status, $agent_id, $grade_id);

            $agent_name = $grade_id == 1 ? '厅主' : '代理商';
            @addLog(['action_name'=>'修改'.$agent_name.'的状态','action_desc'=>' 对 '.$agent_name.$user['user_name'].'的状态进行修改，状态被置为'.$attributes['account_lock'],'action_passivity'=> $user['user_name']]);
            return $this->response->array([
                'code'=>0,
                'text'=> trans('agent.success'),
                'result'=>'',
            ]);

        }

        return $this->response->array([
            'code' => 400,
            'text' => trans('agent.save_fails'),
            'result' => '',
        ]);

    }

    /**
     * @api {post} /agent/{agent_id}/menus 保存代理商菜单权限
     * @apiDescription 代理商编辑菜单权限 agent_id:代理商id
     * @apiGroup agent
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {array} menus ['2-1','33-2']['id-parent_id']
     * @apiSuccessExample {json} 成功返回格式
        {
        "code": 0,
        "text": "保存成功",
        "result": ""
        }
     */
    public function setMenuRole(Request $request, int $agent_id)
    {
        $agent = Agent::where(['id'=>$agent_id,'is_hall_sub' =>0])->first();
        //代理商验证
        if( ! $agent ) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.agent_not_exist'),
                'result'=>'',
            ]);
        }
        $data = $request->input('menus');
        $re = self::openMenuRole($data,$agent_id);
        if($re['code']) {

            $agent_name = $agent['grade_id'] == 1 ? '厅主' : '代理商';
            @addLog(['action_name'=>'修改'.$agent_name.'的权限','action_desc'=>' 对 '.$agent_name.$agent['user_name'].'的权限进行修改','action_passivity'=> $agent['user_name']]);

            return $this->response->array([
                'code'=>0,
                'text'=> trans('agent.success'),
                'result'=>'',
            ]);
        } else {
            return $this->response->array($re['data']);
        }

    }
    /**
     * @param array $data 菜单数据格式 ['2-1','33-2']，[id-parent_id]
     * @param int $agent_id 厅主id
     * @return array
     */
    protected function openMenuRole( $data , int $agent_id)
    {
        if( $data ) {
            $data_arr = [];
            foreach ($data as $menu) {

                $game_tmp = explode('-',$menu);
                $data_arr[] = [
                    'user_id'=> $agent_id,
                    'menu_id' => $game_tmp[0],
                    'parent_id' => $game_tmp[1],
                ];

            }

            $re = AgentMenus::where('user_id',$agent_id)->get();
            $re && AgentMenus::where('user_id',$agent_id)->delete();
            $res_game = AgentMenus::insert($data_arr);
            if ($res_game) {
                return [
                    'code' => 1
                ];
            }

            return [
                'code' => 0,
                'data' => [
                    'code' => 400,
                    'text' => trans('agent.save_fails'),
                    'result' => '',
                ]
            ];
        }
        $re = AgentMenus::where('user_id',$agent_id)->get();
        $re && AgentMenus::where('user_id',$agent_id)->delete();
        return [
            'code' => 1
        ];
    }
    /**
     * @param array $data 厅游戏数据 格式['0-1-1','1-2-1']
     * @param int $agent_id 厅主id
     * @return array
     */
    protected function openHallGame( array $data , int $agent_id) :array
    {
        if( $data ) {

            $data_arr = [];
            foreach ($data as $game) {

                $game_tmp = explode('-',$game);
                $data_arr[] = [
                    'agent_id'=> $agent_id,
                    'game_id' => $game_tmp[0],
                    'hall_id' => $game_tmp[1],
                    'status' =>  $game_tmp[2],
                ];

            }
            $re = AgentGame::where('agent_id',$agent_id)->get();
            $re && AgentGame::where('agent_id',$agent_id)->delete();
            $res_game = AgentGame::insert($data_arr);
            if ($res_game) {
                return [
                    'code' => 1
                ];
            }

            return [
                'code' => 0,
                'data' => [
                    'code' => 400,
                    'text' => trans('agent.save_fails'),
                    'result' => '',
                ]
            ];
        } else {
            $re = AgentGame::where('agent_id',$agent_id)->get();
            $re && AgentGame::where('agent_id',$agent_id)->delete();
        }
        return [
            'code' => 1
        ];

    }

    /**
     * @param array $data 游戏分成 格式：
        [
            [
                "start_profit" => "0.00",//毛利润开始值
                "end_profit" => "100.00",//毛利润结束值
                "scale" => "30"//站成比例，单位：%
            ]
        ]
     * @param int $agent_id 厅主id
     * @return array
     */
    public function setGameScale( array $data, int $agent_id ) : array
    {
        if( $data ) {

            $time = date('Y-m-d H:i:s', time());
            $count = count($data) - 1;
            foreach ($data as $k => &$scale) {
                //两条数据时，判断后一条数据最大值要等于前一条数据的最大值
                if( isset( $data[$k+1] ) ) {
                    if( $data[$k]['end_profit'] != $data[$k+1]['start_profit'] ) {
                        return [
                            'code' => -1,
                            'data' => [
                                'code' => 400,
                                'text' => trans('agent.last_max_next_min'),
                                'result' => '',
                            ]
                        ];
                    }
                }
                //判断不是最后一条时，最小值不能大于等于最大值
                if( $count != $k ) {
                    if ( (double)$scale['start_profit'] >= (double)$scale['end_profit'] ) {

                        return [
                            'code' => -1,
                            'data' => [
                                'code' => 400,
                                'text' => trans('agent.min_max_error'),
                                'result' => '',
                            ]
                        ];

                    }
                } else {
                    //最后一条最大值必须为空
                    if( (double)$scale['end_profit'] ) {
                        return [
                            'code' => -1,
                            'data' => [
                                'code' => 400,
                                'text' => trans('agent.last_max_error'),
                                'result' => '',
                            ]
                        ];
                        /*if ( (double)$scale['start_profit'] >= (double)$scale['end_profit'] ) {

                            return [
                                'code' => -1,
                                'data' => [
                                    'code' => 400,
                                    'text' => trans('agent.min_max_error'),
                                    'result' => '',
                                ]
                            ];

                        }*/
                    }
                }

                if (!is_numeric($scale['scale']) || $scale['scale'] <= 0) {
                    return [
                        'code' => -1,
                        'data' => [
                            'code' => 400,
                            'text' => trans('agent.scale_error'),
                            'result' => '',
                        ]
                    ];
                }
                $scale['add_user'] = \Auth::user()->user_name;
                $scale['add_date'] = $time;
                $scale['p_id'] = $agent_id;
            }

            GameScale::where('p_id', $agent_id)->delete();
            $re_scale = GameScale::insert($data);
            if ($re_scale) {
                return [
                    'code' => 1
                ];
            }

            return [
                'code' => 0,
                'data' => [
                    'code' => 400,
                    'text' => trans('agent.save_fails'),
                    'result' => '',
                ]
            ];
        }

        return [
            'code' => 1
        ];
    }

    /**
     * 游戏费用设置
     * @param array $data 游戏费用 格式
        [
            "roundot" => "30000.00",//包网费
            "line_map" => "30000.00",//线路图
            "upkeep" => "30000.00",//维护费用
            "ladle_bottom" => "30000.00"//包底
        ]
     * @param int $agent_id
     * @return array
     */
    protected function setGameCost( array $data, int $agent_id ) : array
    {
        if( $data ) {

            $time = date('Y-m-d H:i:s', time());
            $data['p_id'] = $agent_id;
            $data['add_user'] = \Auth::user()->user_name;
            $data['update_date'] = $time;
            GameCost::where('p_id',$agent_id)->delete();
            $re_cost = GameCost::insert($data);
            if ($re_cost) {
                return [
                    'code' => 1
                ];
            }

            return [
                'code' => 0,
                'data' => [
                    'code' => 400,
                    'text' => trans('agent.save_fails'),
                    'result' => '',
                ]
            ];
        }

        return [
            'code' => 1
        ];
    }
    /**
     * @api {post} /agents/{agent_id} 获取厅主（代理商）详情
     * @apiDescription 获取厅主（代理商）详情
     * @apiGroup agent
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} grade_id 代理级别，总代则为1，2为二级代理
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
                "code": 0,
                "text": "ok",
                "result": {
                    "agent": {
                        "id": 9,//厅主、代理id
                        "user_name": "anchen2",//登录名
                        "real_name": null,//昵称
                        "desc": null,//描述
                        "grade_id": 2,//代理类型
                        "tel": null,//手机
                        "account_state": 1,//账户状态
                        "add_time": "2017-02-04 09:23:54",//添加时间
                        "update_time": "2017-02-05 02:13:12",//更新时间
                        "ip_info": "127.0.0.1",//ip
                        "parent_id": 0,//上级代理id
                        "mapping": null,
                        "sub_count": 0,//代理数
                        "area": "中国深圳",//运营地区
                        "tel_pre": "87",//手机国家代码
                        "email": "2222@qq.com",//邮箱
                        "account_lock": 0,//是否锁定,1为永久锁定,0为未锁定,7为锁定7天,30为锁定30天
                        "lock_rank": null,//1为锁定其下级代理商，2:为锁定其下子账户,3为锁定其下级玩家
                        "charge_mode": null,
                        "charge_fixed": null,
                        "charge_percentage": null,
                        "time_zone": "(GMT 08:00) Asia / Beijing",//时区
                        "lang_code": "zh_cn1",//语言代码,如简体中文为zh-cn
                        "sub_user": 0,//玩家数
                        "lock_reason": "",//锁定原因
                        "account_type": 0,//账号种类,1为正常账号,2为测试账号
                        "agent_code": "",
                        "t_id": 1,//模板id
                        "group_id": 0//权限分组ID
                    }
                }
         }
     * @apiErrorExample  {json} Error-Response:
     *     HTTP/1.1 200 OK
     *     {
    "code": 400,
    "text": "参数值错误",
    "result": ""
    }
     */
    public  function agentShow(Request $request, $agent_id)
    {
        $validator = \Validator::make($request->all(), [
            'grade_id' => 'required|Integer',
        ]);

        if ($validator->fails()) {

            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.grade_id_error'),
                'result'=>'',
            ]);

        }
        $grade_id = $request->input('grade_id');
        $data = Agent::where(['grade_id'=>$grade_id, 'id'=>$agent_id])->first();

        if($data) {

            return $this->response->array([
                'code'=>0,
                'text'=> trans('agent.success'),
                'result'=>[
                    'agent' => $data,
                ],
            ]);

        } else {

            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.agent_not_exist'),
                'result'=>'',
            ]);

        }

    }

    /**
     * @api {post} /agents/{agent_id}/password 厅主（代理商）修改密码
     * @apiDescription 厅主（代理商）修改密码
     * @apiGroup agent
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} grade_id 代理级别，总代则为1，2为二级代理
     * @apiParam {String} password 新密码
     * @apiParam {String} password_confirmation 确认新密码
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     */
    public function password(Request $request, $agent_id)
    {

        $validator = \Validator::make($request->input(), [
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6',
            'grade_id' => 'required|integer|in:1,2',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors(),
                'result'=>'',
            ]);
        }
        $grade_id = $request->input('grade_id');
        $password = $request->input('password');

        $angent_info = Agent::find($agent_id);

        if( ! $angent_info ) {

            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.agent_not_exist'),
                'result'=>'',
            ]);

        }

        $password = app('hash')->make($password.$angent_info->salt);

        $re = Agent::where(['id'=>$agent_id,'grade_id'=>$grade_id])->update(['password'=>$password]);
        if($re){
            @addLog(['action_name'=>'修改密码','action_desc'=>' 对厅主 '.$angent_info->user_name.' 修改了密码','action_passivity'=>$angent_info->user_name]);
            return $this->response->array([
                'code'=>0,
                'text'=>trans('agent.success'),
                'result'=>'',
            ]);
        } else {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.save_fails'),
                'result'=>'',
            ]);
        }
    }


    /**
     * @api {get} agent/menu/{grade_id} 获取代理商菜单权限列表数据
     * @apiDescription 获代理商菜单权限 grade_id：代理商级别，1：厅主，2：代理商
     * @apiGroup agent
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {Number} agent_id 代理商id
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "data": [
    {
    "id": 1,
    "parent_id": 0,
    "title_cn": "账号管理",
    "title_en": "",
    "class": 0,
    "desc": null,
    "link_url": "/accountManage",
    "icon": "icon-guanli",
    "state": 1,
    "sort_id": 1,
    "menu_code": "M1001",
    "update_date": null,
    "is_have": 1,
    "_child": [
        {
        "id": 2,//菜单id
        "parent_id": 1,//上级菜单id
        "title_cn": "代理商管理",
        "title_en": "",
        "class": 0,//菜单类型分类，0为通用菜单，1为厅主类菜单，2为代理类菜单，默认为0通用菜单
        "desc": null,
        "link_url": "/accountManage/AgentM",
        "icon": "icon-zuanshi",
        "state": 1,
        "sort_id": 1,
        "menu_code": "M1003",
        "update_date": "2017-05-11 04:40:32",
        "is_have": 1,//是否选中 1是，0否
        "_child": [
                {
                    "id": 33,
                    "parent_id": 2,
                    "title_cn": "添加代理商",
                    "title_en": "",
                    "class": 0,
                    "desc": null,
                    "link_url": "/accountManage/AgentM/Add",
                    "icon": "",
                    "state": 1,
                    "sort_id": 1,
                    "menu_code": null,
                    "update_date": "2017-07-14 11:11:28",
                    "is_have": 1
                }
            ]
        }
        ]
    }
    ]
    }
    }
     */
    public function getAgentMenu(Request $request, int $grade_id)
    {
        $agent_id  = (int)$request->input('agent_id');
        //获取当前代理商类型所属的菜单列表[menu_id，parent_id]
        $agent_menu_list = AgentMenuList::select('menu_id','parent_id')->where('grade_id',$grade_id)->where('state',1)->get();
        //把菜单menu_id,parent_id存到一个数组里面
        $menu_list = [];
        if( $agent_menu_list ) {
            foreach ($agent_menu_list as $item) {
                $menu_list[] = $item['menu_id'];
                $menu_list[] = $item['parent_id'];
            }
            //去重
            $menu_list = array_unique($menu_list);
        }
        //获取总菜单且属于该代理商类型的菜单
        $menus = AgentMenu::where('state' ,1)->whereIn('id', $menu_list)->get();
        //声明一个菜单id数组
        $menu_ids = [];
        //具体代理商
        if ($agent_id ) {
            //根据代理商id获取所属的菜单数据['menu_id','parent_id']
            $AgentMenus = AgentMenus::select('menu_id','parent_id')->where('user_id', $agent_id)->get();
            foreach ($AgentMenus as $item) {
                $menu_ids[] = $item['menu_id'];
                $menu_ids[] = $item['parent_id'];
            }
            //存到菜单id数组，并去重
            $menu_ids = array_unique($menu_ids);

        }
        //在总菜单处理该代理商相对应的菜单上加字段标识，方便前端显示
        $menus = $menus->each(function ($item) use($menu_ids, $agent_id) {

            if( count($menu_ids) ) {
                if( in_array($item['id'], $menu_ids)) {
                    $item['is_have'] = 1;
                } else {
                    $item['is_have'] = 0;
                }
            } else {
                if( ! $agent_id ) {
                    $item['is_have'] = 1;
                } else {
                    $item['is_have'] = 0;
                }
            }

        });
        return $this->response->array([
            'code'=>0,
            'text'=>trans('agent.success'),
            'result'=>['data' => list_to_tree($menus->toArray(), 'id','parent_id')],
        ]);
    }

    /**
     * @param $hall_name
     */
    public static  function setHallGame($agent_id){
        $keyName = 'agent_game:'. (int)$agent_id;

        // 查询当前的厅主开通的游戏（关联游戏表）
        $agentGameInfo = DB::table('agent_game')->join('game_info','agent_game.game_id','=','game_info.id')->select(['agent_game.game_id','agent_game.hall_id'])->where('agent_game.agent_id', $agent_id)->where('agent_game.status',1)->where('game_info.status',1)->get();

        $redis = Redis::connection("default");
        $redis->del($keyName);

        if( count($agentGameInfo)) {
            $data = [];
            foreach (StringShiftToInt($agentGameInfo,["game_id","hall_id"]) as $item){
                $data[] = json_encode($item);
            }
            $redis->rpush($keyName, $data);
        }

    }
}
