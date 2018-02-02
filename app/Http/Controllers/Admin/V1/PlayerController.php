<?php
/**
 * Created by PhpStorm.
 * User: anchen
 * Date: 2017/2/5
 * Time: 17:57
 * Desc 玩家控制器
 */
namespace App\Http\Controllers\Admin\V1;

use App\Models\UserChartInfo;
use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\Agent;
use App\Models\CashRecord;
use App\Models\PlayerOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use MongoDB\BSON\UTCDateTime;

class PlayerController extends BaseController
{
    public function __construct()
    {
    }

    /**
     * @api {get} /player 玩家管理列表
     * @apiDescription 玩家管理列表
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token *
     * @apiParam {String} locale 语言 *
     * @apiParam {String} user_name 用户名，玩家在第三方平台账号
     * @apiParam {Number} uid 用户id
     * @apiParam {String} start_add_time 注册开始时间 2017-01-20 15:07:07
     * @apiParam {String} end_add_time 注册结束时间 2017-01-20 15:07:07
     * @apiParam {String} account_state 状态 账号状态,1为正常,2为暂停使用,3为停用,4为逻辑删除
     * @apiParam {Number} page 当前页
     * @apiParam {Number} page_num 每页条数
     * @apiParam {Number} is_page 是否分页 0否，1是，默认1分页
     * @ apiSampleRequest http://app-loc.dev/api/player
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
         {
            "code": 0,
            "text": "操作成功",
            "result": {
            "total": 1,
            "per_page": 10,
            "current_page": 1,
            "last_page": 1,
            "next_page_url": null,
            "prev_page_url": null,
            "from": 1,
            "to": 1,
            "data": [
                {
                    "uid": 1,
                    "user_name": "csj_play",
                    "username_md": "csj_play_3",
                    "alias": "我来也",
                    "hall_id": 1,
                    "agent_id": 0,
                    "add_date": "2017-01-20 15:07:03",
                    "account_state": 1,
                    "connect_mode": 1, //值为1则为共享钱包模式
                }
            ]
            }
        }
     */
    public function index(Request $request){

        $user_name = $request->input('user_name');
        $uid = $request->input('uid');
        $start_add_time = $request->input('start_add_time');
        $end_add_time = $request->input('end_add_time');
        $account_state = $request->input('account_state');
        $page_num = $request->input('page_num',10);
        $is_page = $request->input('is_page',1);

        $db = Player::select('lb_user.uid','lb_user.user_name','lb_user.username_md','lb_user.alias','lb_user.hall_id','lb_user.agent_id','lb_user.add_date','lb_user.account_state','lb_user.money','lb_user.hall_name','lb_user.agent_name','lb_user.on_line','au.connect_mode')
                ->leftJoin("lb_agent_user as au",function($join){
                    $join->on('lb_user.hall_id','=','au.id');
                });

        //获取测试，联调代理id
        $ids = Agent::where(['grade_id' => 2, 'is_hall_sub' => 0])->whereIn('account_type',[2,3])->pluck('id');
        $db->whereNotIn('lb_user.agent_id', $ids);
        $db->where('lb_user.user_rank','<>',2);
        if(isset($user_name) && !empty($user_name)) {
//            $db->where('username_md','like', '%'.decrypt_($user_name).'%');
            $db->where('lb_user.username_md', decrypt_($user_name));
        }

        if(isset($uid) && !empty($uid)) {
            $db->where('lb_user.uid',$uid);
        }
        if(isset($start_add_time) && !empty($start_add_time)) {
            $db->where('lb_user.add_date', '>=', $start_add_time);
        }
        if(isset($end_add_time) && !empty($end_add_time)) {
            $db->where('lb_user.add_date', '<', $end_add_time);
        }
        if(isset($account_state) && $account_state !== '') {
            $db->where('lb_user.account_state',$account_state);
        }
        $db->orderby('lb_user.add_date','desc');

        if($is_page) {
            $player = $db->paginate($page_num);
        } else {
            $player = $db->get();
        }

        foreach ($player as &$v) {
            $v->user_name = encrypt_($v->user_name);
            $v->username_md = encrypt_($v->username_md);
        }

        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => $is_page ? $player : ['data' => $player],
        ]);
    }

    /**
     * @api {get} /admin/player 玩家管理员列表
     * @apiDescription 玩家管理员列表
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token *
     * @apiParam {String} locale 语言 *
     * @apiParam {String} user_name 用户名，玩家在第三方平台账号
     * @apiParam {Number} uid 用户id
     * @apiParam {String} start_add_time 注册开始时间 2017-01-20 15:07:07
     * @apiParam {String} end_add_time 注册结束时间 2017-01-20 15:07:07
     * @apiParam {String} account_state 状态 账号状态,1为正常,2为暂停使用,3为停用,4为逻辑删除
     * @apiParam {Number} page 当前页
     * @apiParam {Number} page_num 每页条数
     * @apiParam {Number} is_page 是否分页 0否，1是，默认1分页
     * @ apiSampleRequest http://app-loc.dev/api/player
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 1,
    "per_page": 10,
    "current_page": 1,
    "last_page": 1,
    "next_page_url": null,
    "prev_page_url": null,
    "from": 1,
    "to": 1,
    "data": [
    {
    "uid": 1,
    "user_name": "csj_play",
    "username_md": "csj_play_3",
    "alias": "我来也",
    "hall_id": 1,
    "agent_id": 0,
    "add_date": "2017-01-20 15:07:03",
    "account_state": 1
    }
    ]
    }
    }
     */
    public function adminTest(Request $request) {
        $user_name = $request->input('user_name');
        $uid = $request->input('uid');
        $start_add_time = $request->input('start_add_time');
        $end_add_time = $request->input('end_add_time');
        $account_state = $request->input('account_state');
        $page_num = $request->input('page_num',10);
        $is_page = $request->input('is_page',1);

        $db = Player::select('uid','user_name','username_md','alias','hall_id','agent_id','add_date','account_state','money','hall_name','agent_name','on_line');

        $db->where('user_rank',2);
        if(isset($user_name) && !empty($user_name)) {
            $db->where('username_md','like', '%'.decrypt_($user_name).'%');
        }

        if(isset($uid) && !empty($uid)) {
            $db->where('uid',$uid);
        }
        if(isset($start_add_time) && !empty($start_add_time)) {
            $db->where('add_date', '>=', $start_add_time);
        }
        if(isset($end_add_time) && !empty($end_add_time)) {
            $db->where('add_date', '<', $end_add_time);
        }
        if(isset($account_state) && $account_state !== '') {
            $db->where('account_state',$account_state);
        }
        $db->orderby('add_date','desc');

        if($is_page) {
            $player = $db->paginate($page_num);
        } else {
            $player = $db->get();
        }

        foreach ($player as &$v) {
            $v->user_name = encrypt_($v->user_name);
            $v->username_md = encrypt_($v->username_md);
        }

        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => $is_page ? $player : ['data' => $player],
        ]);
    }

    /**
     * @api {post} /player 添加玩家
     * @apiDescription 添加玩家
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} user_name 登录名
     * @apiParam {String} alias 用户名
     * @apiParam {String} password_md 密码
     * @apiParam {String} password_md_confirmation 确认密码
     * @apiParam {String} agent_name 直属代理名称
     * @apiParam {Number} account_state 账号状态,1为正常,2为暂停使用,3为停用,4为逻辑删除
     * @ apiSampleRequest http://app-loc.dev/api/player
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "保存成功",
    "result": ""
    }
     */
    public function store(Request $request)
    {

        $message = [
            'alias.required' => trans('agent.alias')
        ];
        $validator = \Validator::make($request->input(), [
            'user_name' => 'required|unique:lb_user',
            'password_md' => 'required_without:user_id|max:6|confirmed',
            'password_md_confirmation' => 'required_without:user_id|max:6',
            'agent_name' => 'required',
            'account_state' => 'required',
            'alias' => 'required',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $attributes = $request->except('token','locale','password_md_confirmation','agent_name');

        if(!preg_match('/^[a-zA-z][a-zA-Z0-9_]{5,19}$/',$attributes['user_name'])) {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.user_name'),
                'result'=>'',
            ]);
        }

        $user_name = $request->input('user_name');

        if( Player::where('user_name',decrypt_($user_name))->first() ) {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.user_has_exist'),
                'result'=>'',
            ]);
        }

        //代理商
        $agent_name = $request->input('agent_name');
        $agent = Agent::where(['user_name' => $agent_name, 'is_hall_sub' =>0, 'grade_id' => 2])->first();

        if( ! $agent ) {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.agent_not_exist'),
                'result'=> '',
            ]);
        }
        $attributes['user_name'] = decrypt_($agent->agent_code.$attributes['user_name']);
        $attributes['username_md'] = decrypt_($attributes['user_name']);
        $attributes['add_ip'] = $request->ip();

        if( $hall_id = $agent->parent_id ){

            $hall = Agent::where('id', $hall_id)->first();
            $attributes['hall_id'] = $hall->id;
            $attributes['hall_name'] = $hall->user_name;
        }

        $attributes['agent_id'] = $agent->id;
        $attributes['agent_name'] = $agent->user_name;

        $attributes['salt'] = randomkeys(20);

        if(isset($attributes['password_md'])){
            $attributes['password_md'] = decrypt_($attributes['password_md']);
            $attributes['password'] = $attributes['password_md'];
        }
        $attributes['add_date'] = date('Y-m-d H:i:s',time());
        $attributes['create_time'] = date('Y-m-d H:i:s',time());
        $user = Player::create($attributes);

        if( !$user ){
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.add_fails'),
                'result' => '',
            ]);
        }
        //所属的厅主、代理相应累计玩家数
        Agent::where('user_name',$agent_name)->increment('sub_user');
        Agent::where('id',$attributes['hall_id'])->increment('sub_user');
        @addLog(['action_name'=>'添加玩家','action_desc'=>' 添加了一个新游戏玩家，玩家账号为:'.$request->input('user_name'),'action_passivity'=>$request->input('user_name')]);

        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => '',
        ]);

    }

    /**
     * @api {put} /player/{id} 编辑玩家
     * @apiDescription 编辑玩家
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {Number} account_state 账号状态,1为正常,2为暂停使用,3为停用,4为逻辑删除
     * @ apiSampleRequest http://app-loc.dev/api/player
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "保存成功",
    "result": ""
    }
     */
    public function update(Request $request, int $id)
    {
        $player = Player::select('uid')->where(['uid' => $id])->first();
        if( ! $player ) {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.user_not_exist'),
                'result'=> '',
            ]);
        }

        $validator = \Validator::make($request->input(), [
            'account_state' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }
        $user = Player::where('uid',$id)->update(['account_state' => $request->input('account_state')]);
        switch ($request->input('account_state'))
        {
            case 1:
                @addLog(['action_name'=>'恢复正常玩家','action_desc'=>' 恢复正常玩家状态，玩家账号为:'.$request->input('user_name'),'action_passivity'=>$request->input('user_name')]);
                break;
            case 2:
                @addLog(['action_name'=>'暂停使用玩家','action_desc'=>' 暂停使用了一个游戏玩家，玩家账号为:'.$request->input('user_name'),'action_passivity'=>$request->input('user_name')]);
                break;
            case 3:
                @addLog(['action_name'=>'停用玩家','action_desc'=>' 停用了一个游戏玩家，玩家账号为:'.$request->input('user_name'),'action_passivity'=>$request->input('user_name')]);
                break;
            case 4:
                @addLog(['action_name'=>'登出玩家','action_desc'=>' 登出了一个游戏玩家，玩家账号为:'.$request->input('user_name'),'action_passivity'=>$request->input('user_name')]);
                break;
        }

        if( $user === false) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.save_fails'),
                'result' => '',
            ]);
        }

        self::updatePlayerInfoRedis($id);

        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.save_success'),
            'result' => '',
        ]);

    }
    /**
     * 更新玩家redis信息
     * @param int $id 玩家id
     */
    private function updatePlayerInfoRedis(int $id)
    {
        $info = Player::find($id);
        if($info) {
            $info = StringShiftToInt($info->toArray(),['user_rank','account_state','hall_id','agent_id','profit_share_platform','profit_share_agent','profit_share_hall','money','grand_total_money']);
        }
        $agent = Agent::select('agent_code')->find($info['agent_id']);
        $session_id = md5( $info['user_name'] );
        $uid = substr( $session_id, 0, 21 );

        $user_name = $info['user_name'];
        $info['user_name'] = encrypt_($user_name);
        $info['username_md'] = encrypt_($info['username_md']);
        $info['username2'] = $user_name;
        $info['time'] = time();
        $info['agent_code'] = $agent['agent_code'];

        $redis = Redis::connection("account");

        if( $re = $redis->get($uid) ) {

            $redis->set($uid, json_encode($info));
        }

    }

    /**
     * @api {post} /player/{user_id} 编辑玩家时获取数据
     * @apiDescription 编辑玩家时获取数据
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} locale 语言
     * @ apiSampleRequest http://app-loc.dev/api/player/191
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
          {
            "code": 0,
            "text": "操作成功",
            "result": {
            "data": {
            "uid": 191,
            "user_name": "",
            "username_md": "111111",
            "password_mb_c": null,
            "password_mb_s": "",
            "alias": "人生玩家",
            "add_date": "2017-02-06 07:17:08",
            "create_time": "0000-00-00 00:00:00",
            "last_time": "0000-00-00 00:00:00",
            "add_ip": "127.0.0.1",
            "ip_info": "0.0.0.0",
            "on_line": "N",
            "account_state": 1,
            "hall_id": 1,
            "agent_id": 2,
            "hall_name": "csj",
            "agent_name": "c112",
            "mapping": null,
            "profit_share_platform": null,
            "profit_share_agent": "0",
            "profit_share_hall": "0",
            "money": null,
            "token_id": null,
            "is_test": 0,
            "language": "zh-cn"
            }
            }
        }
     */
    public function show(Request $request, $user_id)
    {
        $player = Player::where('uid',$user_id)->first();
        if($player){
            $player->account_state = (string)$player->account_state;
            $player->agent_id = (string)$player->agent_id;
            $player->user_name = encrypt_($player->user_name);
            $player->username_md = encrypt_($player->username_md);
            $player = $player->toArray();
        }
        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => [
                'data' => $player,
            ],
        ]);
    }

    /**
     * @api {post} /player/{user_id}/password 修改玩家密码
     * @apiDescription 修改玩家密码
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} password_md 密码
     * @apiParam {String} password_md_confirmation 确认密码
     * @ apiSampleRequest http://app-loc.dev/api/player/191/password
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
        {
        "code": 0,
        "text": "保存成功",
        "result": ""
        }
     */
    public function password(Request $request, $user_id)
    {
        $validator = \Validator::make($request->input(), [
            'password_md' => [
                'required',
                'min:6',
                'max:12',
                'regex:/^[0-9a-zA-Z]{6,12}$/',
                'confirmed'
            ],
            'password_md_confirmation' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors(),
                'result'=>'',
            ]);
        }

        $user = Player::where(['uid'=>$user_id])->first();
        if( ! $user ) {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.user_not_exist'),
                'result'=>'',
            ]);
        }

        if( ! $salt = $user->salt ) {
            $salt = randomkeys(20);
            Player::where(['uid'=>$user_id])->update(['salt'=>$salt]);
        }

        $password = decrypt_($request->input('password_md'));

        $re = Player::where(['uid'=>$user_id])->update(['password'=>$password,'password_md' => $password]);

        if($re){
            @addLog(['action_name'=>'修改玩家密码','action_desc'=>' 修改了游戏玩家密码，玩家账号为:'.encrypt_($user->user_name),'action_passivity'=>encrypt_($user->user_name)]);

            self::updatePlayerInfoRedis($user_id);

            return $this->response->array([
                'code'=>0,
                'text'=>trans('agent.save_success'),
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
     * @api {patch} /player/{id}/status 修改玩家状态
     * @apiDescription 修改玩家状态（1启用、2冻结、3停用、4登出）
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} account_state 状态值 1启用、2冻结、3停用
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "保存成功",
    "result": ""
    }
     */
    public function statusUpdate(Request $request, int $id)
    {
        $account_state = $request->input('account_state');

        $where = [
            'uid' => $id,
        ];

        $user = Player::where($where)->first();

        if( !$user ) {

            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.user_not_exist'),
                'result'=>'',
            ]);

        }

        $saveData = [];
        if( !in_array($account_state, [1,2,3]) ) {

            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.param_error'),
                'result'=>'',
            ]);

        }
        $saveData['account_state'] = $account_state;
        /*if($account_state == 4) {
            $saveData['on_line'] = 'N';
        }*/
        $re = Player::where($where)->update($saveData);

        if( $re !== false ){

            self::updatePlayerInfoRedis($id);
            switch ($request->input('account_state'))
            {
                case 1:
                    @addLog(['action_name'=>'恢复正常玩家','action_desc'=>' 恢复正常玩家状态，玩家账号为:'.encrypt_($user->user_name),'action_passivity'=>encrypt_($user->user_name)]);
                    break;
                case 2:
                    @addLog(['action_name'=>'暂停使用玩家','action_desc'=>' 暂停使用了一个游戏玩家，玩家账号为:'.encrypt_($user->user_name),'action_passivity'=>encrypt_($user->user_name)]);
                    //玩家停用操作进行推送给服务端
                    //写入队列中
                    $msg = json_encode(['cmd'=>'KickPlayer','accountId'=>$id]);
                    $re = RabbitmqController::publishMsg([env('MQ_SERVER_CHANNEL'),env('MQ_SERVER_QUEUE'),env('MQ_SERVER_KEY'),$msg]);

                    break;
                case 3:
                    @addLog(['action_name'=>'停用玩家','action_desc'=>' 停用了一个游戏玩家，玩家账号为:'.encrypt_($user->user_name),'action_passivity'=>encrypt_($user->user_name)]);
                    break;
                case 4:
                    @addLog(['action_name'=>'登出玩家','action_desc'=>' 登出了一个游戏玩家，玩家账号为:'.encrypt_($user->user_name),'action_passivity'=>encrypt_($user->user_name)]);
                    break;
            }
            return $this->response->array([
                'code'=>0,
                'text'=>trans('agent.save_success'),
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
     * @api {patch} /player/{id}/onLine 玩家登出
     * @apiDescription 玩家登出
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "保存成功",
    "result": ""
    }
     */
    public function signOut(Request $request, int $id)
    {
        $where = [
            'uid' => $id,
        ];

        $user = Player::where($where)->first();

        if( !$user ) {

            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.user_not_exist'),
                'result'=>'',
            ]);

        }

        if( $user->on_line == 'N' ) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.user_sign_out'),
                'result'=>'',
            ]);
        }

        //写入队列中
        $msg = json_encode(['cmd'=>'KickPlayer','accountId'=>$id]);
        $re = RabbitmqController::publishMsg([env('MQ_SERVER_CHANNEL'),env('MQ_SERVER_QUEUE'),env('MQ_SERVER_KEY'),$msg]);

        if( $re ) {

            //Player::where($where)->update(['on_line' => 'N']);

            self::updatePlayerInfoRedis($id);

            @addLog(['action_name'=>'登出玩家','action_desc'=>' 登出了一个游戏玩家，玩家账号为:'.encrypt_($user->user_name),'action_passivity'=>encrypt_($user->user_name)]);

            return $this->response->array([
                'code'=>0,
                'text'=>trans('agent.success'),
                'result'=>'',
            ]);

        }

        return $this->response->array([
            'code'=>400,
            'text'=>trans('agent.fails'),
            'result'=>'',
        ]);
    }
    /**
     * @api {get} /player/{user_id}/balance 查询玩家余额
     * @apiDescription 查询玩家余额
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} locale 语言
     * @ apiSampleRequest http://app-loc.dev/api/player/191/balance
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
        {
            "code": 0,
            "text": "操作成功",
            "result": {
                "balance": {
                    "uid": 191,
                    "money": "2000.00",
                    "username_md": "111111",
                    "add_date": "2017-02-06 15:17:08",
                    "last_update_time": "2017-02-07 13:59:45"
                }
            }
        }
     */
    public function balance(Request $request, $user_id)
    {
        $user = Player::select('uid','money','username_md','add_date','last_update_time')->where('uid',$user_id)->first();

        if($user) {

            $user->where('uid',$user_id)->update(['last_update_time' =>  Carbon::now()]);

            self::updatePlayerInfoRedis($user_id);

            return $this->response->array([
                'code'=>0,
                'text'=>trans('agent.success'),
                'result'=>[
                    'balance' =>$user,
                ],
            ]);

        } else {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.fails'),
                'result'=>'',
            ]);
        }
    }

    /**
     * @api {post} /player/{user_id}balance 玩家余额扣取（充值）
     * @apiDescription 查询玩家余额
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} money 金额
     * @apiParam {Number} status 加减状态，3是加，4是减
     * @ apiSampleRequest http://app-loc.dev/api/player/191/balance
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
         {
            "code": 0,
            "text": "操作成功",
            "result": ""
        }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 200 OK
        {
        "code": 400,
        "text": {
        "status": [
        "status 不能为空。"
        ]
        },
        "result": ""
        }
     */
    public function balanceHandle(Request $request, $user_id)
    {


        $validator = \Validator::make($request->input(), [
            'money' => 'required|numeric|min:1',
            'status' => 'required|in:3,4',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }
        $status = $request->input('status');

        $money = sprintf("%.2f", $request->input('money'));

        $user = Player::where('uid',$user_id)->first();

        if(!$user) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.user_not_exist'),
                'result'=>'',
            ]);
        }
//        $agent = Agent::where('id',$user->agent_id)->pluck('user_name');
        $agent = Agent::where('id',$user->agent_id)->select('user_name','agent_code')->first();
//        $agent_name = $agent ? $agent[0] : '';
        $agent_name = isset($agent->agent_code) ? $agent->agent_code : '';

        $isShare = false; //是否为共享钱包模式
        //充值金额
        if(3 == $status) {

            $re = $user->where('uid',$user_id)->increment('money', $money);
            //累计充值余额
            $re && $user->where('uid',$user_id)->update(['grand_total_money' => $user->money + $money]);
            //统计代理商下的玩家充值
            $re && $this->totalScoreRecord($user->agent_id, $money);

            //判断用户所属的厅主是是否为采用共享钱包模式
            $hallInfo = Agent::where('id',$user->hall_id)->select('connect_mode')->first();
            if(isset($hallInfo->connect_mode) && $hallInfo->connect_mode == 1)
            {
                $isShare = true;

                //采用共享钱包模式则进行通知包网平台
                $msgData['agent_id'] = $user->agent_id;
                $msgData['agent_name'] = $agent_name;
                $msgData['user_id'] = $user_id;
                $msgData['user_name'] = str_replace($agent->agent_code,'',encrypt_($user->user_name));
                $msgData['money'] = (double)$money;
                $msgData['remark'] = "LEBO平台充值操作";
                $gameServer = new GameServerController();
                $msg = $gameServer->roundotUserBalanceMessage($msgData,1016);//通知包网平台
            }else{
                $msg = true;
            }
        }
        //扣取金额
        if(4 == $status) {

            if( ($user->money - $money) < 0) {
                return $this->response->array([
                    'code'=>400,
                    'text'=> trans('agent.insufficient_balance'),
                    'result'=>'',
                ]);
            }
            $re = $user->where('uid',$user_id)->decrement('money', $money);
            //累计扣款余额
            $re && $user->where('uid',$user_id)->update(['grand_total_money' => $user->money - $money]);

            //判断用户所属的厅主是是否为采用共享钱包模式
            $hallInfo = Agent::where('id',$user->hall_id)->select('connect_mode')->first();
            if(isset($hallInfo->connect_mode) && $hallInfo->connect_mode == 1)
            {
                $isShare = true;

                //采用共享钱包模式则进行通知包网平台
                $msgData['agent_id'] = $user->agent_id;
                $msgData['agent_name'] = $agent_name;
                $msgData['user_id'] = $user_id;
                $msgData['user_name'] = str_replace($agent->agent_code,'',encrypt_($user->user_name));
                $msgData['money'] = (double)-$money;
                $msgData['remark'] = "LEBO平台扣款操作";
                $gameServer = new GameServerController();
                $msg = $gameServer->roundotUserBalanceMessage($msgData,1016);//通知包网平台
            }else
            {
                $msg = true;
            }
        }

        if($re && $msg) {

            $redis = Redis::connection("monitor");
             //用户增加充值、扣款后累计清除下注次数
            $redis->set("betcount:".$user_id, 0);
            //重新获取玩家余额
            $user_money = Player::where('uid',$user_id)->pluck('money')[0];
            //记录现金表
            $cashRecord = new CashRecord;

            $ordernum = createOrderSn();
            $cashRecord->order_sn = $ordernum;
            $cashRecord->cash_no = $ordernum;
            $cashRecord->uid = (int) $user->uid;
            $cashRecord->agent_id = (int) $user->agent_id;
            $cashRecord->hall_id = (int) $user->hall_id;
            $cashRecord->user_name = encrypt_($user->user_name);//玩家在第三方平台账号
            $cashRecord->type = 4;
            $cashRecord->amount = (double) $money;
            $cashRecord->status = $status;
            $cashRecord->user_money = (double)$user_money;
//            $cashRecord->desc = $status == 3 ? '用户余额充值' : '用户余额扣取';
            $cashRecord->desc = '流水号：'.$ordernum;
            $cashRecord->admin_user = \Auth::user()->user_name;
            $cashRecord->admin_user_id = (int) \Auth::id();
            $cashRecord->add_time = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            $cashRecord->pkey = md5($agent_name.$ordernum.env('PT_API_SUF'));
            $cashRecord->connect_mode = ($isShare == true) ? 1 : 0;
            $cashRecord->save();

            self::updatePlayerInfoRedis($user_id);
            switch ($status)
            {
                case 3:
                    @addLog(['action_name'=>'用户充值','action_desc'=>' 对玩家 '.encrypt_($user->user_name).' 进行了充值 +'.$money,'action_passivity'=>encrypt_($user->user_name)]);
                    break;
                case 4:
                    @addLog(['action_name'=>'用户余额扣取','action_desc'=>' 对玩家 '.encrypt_($user->user_name).' 进行了余额扣除 -'.$money,'action_passivity'=>encrypt_($user->user_name)]);
                    break;
            }

            return $this->response->array([
                'code'=>0,
                'text'=> trans('agent.success'),
                'result'=>'',
            ]);
        }
        return $this->response->array([
            'code'=>400,
            'text'=> trans('agent.save_fails'),
            'result'=>'',
        ]);
    }

    /**
     * 玩家充值时 给代理统计
     * @param int $agent_id 代理商id
     * @param float $money 金额
     * @return int
     */
    private function totalScoreRecord( int $agent_id, $money) : int
    {
        $agent = Agent::select('user_name','parent_id','id')->where(['id' => $agent_id])->first();

        if( $agent ) {
            $hall_agent = Agent::select('user_name')->where(['id' => $agent->parent_id])->first();
            $where = [
                'add_date' => date('Y-m-d', time()),
                'agent_id' => $agent->id
            ];

            $re = \DB::table('statis_cash_agent')->where($where)->first();

            if( ! $re ) {
                $where = [
                    'day_year' => date('Y', time()),
                    'day_month' => date('m', time()),
                    'day_day' => date('d', time()),
                    'agent_id' => $agent->id,
                    'add_date' => date('Y-m-d', time()),
                ];

                $where['hall_id'] = $agent->parent_id;
                $where['agent_name'] = $agent->user_name;
                $where['hall_name'] = $hall_agent->user_name;
                $where['total_score_record'] = $money;

                $res = \DB::table('statis_cash_agent')->insert($where);
                if( $res ) {
                    return 1;
                } else {
                    return -1;
                }
            } else {

                $res = \DB::table('statis_cash_agent')->where($where)->increment('total_score_record', $money);;
                if( $res !== false) {
                    return 1;
                } else {
                    return -1;
                }
            }
        } else {
            return -1;
        }

    }

    //此方法弃用
    public function order(Request $request)
    {
        return '此接口弃用';
        $user_name = $request->input('user_name');
        $user_id = $request->input('user_id');
        $_id = $request->input('id');
        $game_hall_id = $request->input('game_hall_id');
        $game_id = $request->input('game_id');
        $round_no = $request->input('round_no');
        $status = $request->input('status');

        $start_add_time = $request->input('start_add_time');
        //$start_add_time = date("Y-m-d H:i:s",strtotime($request->input('start_add_time')." +8 hour"));
        $end_add_time = $request->input('end_add_time');
//        $end_add_time = date("Y-m-d H:i:s",strtotime($request->input('end_add_time')." +8 hour"));
        $page_num = $request->input('page_num',10);
        $is_page = $request->input('is_page',1);

        $playerOrder = PlayerOrder::select('*', 'add_time->toDateTime()');

        //获取测试，联调代理id
        $ids = Agent::where(['grade_id' => 2, 'is_hall_sub' => 0])->whereIn('account_type',[2,3])->pluck('id');
        $playerOrder->whereNotIn('agent_id', $ids);

        if(isset($user_name) && !empty($user_name)) {
            $playerOrder->where('user_name',$user_name);
        }

        if(isset($_id) && !empty($_id)) {
            $playerOrder->where('cashrecord_id',$_id);
        }

        if(isset($user_id) && !empty($user_id)) {

            $playerOrder->where('user_id',(int)$user_id);
        }

        if(isset($game_hall_id) && $game_hall_id !== '') {

            $playerOrder->where('game_hall_id',(int)$game_hall_id);
        }

        /*if(isset($game_round_id) && !empty($game_round_id)) {

            $playerOrder->where('game_round_id',(int)$game_round_id);
        }*/

        if(isset($round_no) && !empty($round_no)) {

            $playerOrder->where('round_no',$round_no);
        }

        if(isset($game_id) && !empty($game_id)) {

            $playerOrder->where('game_id',(int)$game_id);
        }

        if(isset($status) && !empty($status)) {
            switch ($status) {
                //未取消未派彩
                case 1:
                    $playerOrder->where('is_cancel',0);
                    $playerOrder->where('calculated',0);
                    break;
                //未取消已派彩
                case 2:
                    $playerOrder->where('is_cancel',0);
                    $playerOrder->where('calculated',1);
                    break;
                //已取消未派彩
                case 3:
                    $playerOrder->where('is_cancel',1);
                    $playerOrder->where('calculated',0);
                    break;
                //已取消已派彩
                case 4:
                    $playerOrder->where('is_cancel',1);
                    $playerOrder->where('calculated',1);
                break;
            }
//            $playerOrder->where('status',(int)$status);
        }

        if(isset($start_add_time) && !empty($start_add_time)) {
            $start_add_time = date("Y-m-d H:i:s",strtotime($start_add_time));
            $playerOrder->where('add_time', '>=', new \DateTime($start_add_time));
        }

        if(isset($end_add_time) && !empty($end_add_time)) {

            $end_add_time = date("Y-m-d H:i:s",strtotime($end_add_time));
            $playerOrder->where('add_time', '<', new \DateTime($end_add_time));

        }

        $playerOrder->orderby('add_time','desc');

        if($is_page) {
            $re = $playerOrder->paginate((int)$page_num);
        } else {
            $re = $playerOrder->get();
        }


        //此次由于mongodb保存的时间类型是isodate，返回一个对象，转时间日期时，需要转格式

        $total_score = [
            'bet_money' => 0,
            'bet_money_valid' => 0,
            'payout_win' => 0,
        ];
        foreach ($re as $v){
            $v->add_time = $v->add_time->__tostring();
            $v->add_time = date('Y-m-d H:i:s',$v->add_time/1000);
            !$v['is_cancel'] && $total_score['bet_money'] += $v->bet_money;
            !$v['is_cancel'] && $total_score['bet_money_valid'] += $v->bet_money_valid;
            !$v['is_cancel'] && $total_score['payout_win'] += $v->payout_win;
            $v->bet_money = number_format($v->bet_money, 2);
            $v->bet_money_valid = number_format($v->bet_money_valid, 2);
            $v->payout_win = number_format($v->payout_win, 2);
            $v->odds = number_format($v->odds, 2);
            (time() - strtotime($v->add_time)) > 60 && $v->calculated != 1 && $v->is_cancel != 1  ? $v->is_rollback = 1 : $v->is_rollback = 0; //判断注单是否异常(一分钟还未派彩则为异常数据)
        }

        if( count($total_score) ) {
            $total_score['bet_money'] = number_format($total_score['bet_money']);
            $total_score['bet_money_valid'] = number_format($total_score['bet_money_valid']);
            $total_score['payout_win'] = number_format($total_score['payout_win']);
        }

        if( $is_page ) {
            $re = $re->toArray();
            $re['total_score'] = $total_score;


        } else {
            $re = ['data' => $re,'total_score'=>$total_score];
        }
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$re,
        ]);
    }


    /**
     * @api {get} /player/order 注单查询
     * @apiDescription 注单查询（查user_chart_info表）
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} user_id 玩家id
     * @apiParam {String} account 玩家登录名
     * @apiParam {Number} game_hall_id 游戏厅ID
     * @apiParam {String}  round_no 局ID
     * @apiParam {Number} status 状态 1：未取消未派彩，2：未取消已派彩，3：已取消未派彩，4：已取消已派彩
     * @apiParam {String} start_add_time 下注开始时间
     * @apiParam {String} end_add_time 下注结束时间
     * @apiParam {String} connect_mode 扣费模式：'':全部，0:额度转换，1：共享钱包
     * @apiParam {Number} page_num 每页条数
     * @apiParam {Number} page 当前页
     * @apiParam {Number} is_page 是否分页 1是，0否
     * @ apiSampleRequest http://app-loc.dev/api/player/order
     * @apiSuccessExample {json} 成功返回结果:
    {
    "code": 0,
    "text": "操作成功",
    "result": {
        "total": 1,
        "per_page": 10,
        "current_page": 1,
        "last_page": 1,
        "next_page_url": null,
        "prev_page_url": null,
        "from": 1,
        "to": 1,
        "data": [
            {
                "_id": "5979ce73e138231a1e43e1f0",//记录id
                "total_bet_score": "1,000.00",//投注额
                "total_win_score": "0.00",//派彩额
                "valid_bet_score_total": "1,000.00",//有效投注额
                "cat_id": 1,//游戏分类id
                "start_time": "2017-07-27 07:28:51",//开始时间（下注时间）
                "server_name": "15",//桌号
                "is_cancel": 1,//是否取消，0：否，1：是
                "round_no": "6638b3a2e92a09d1",//局ID
                "game_period": "67-23",//靴-局信息
                "dwRound": 23,//局信息
                "remark": "1;35",//牌信息（游戏结果）
                "account": "D01shenwenzhong",//玩家登录名
                "is_mark": 1,//是否派彩,0：否，1：是
                "game_hall_code": "GH0001",//游戏厅标识码
                "game_name": "龙虎 ",//游戏名称
                "ip_info": "",//IP
                "game_result": '',//游戏结果
                "is_rollback": 0//是否回滚，0：否，1：是
                "connect_mode" :0 //扣费模式：0为额度转换，1为共享钱包，默认为0
            }
        ],
        "total_score": {//小计
            "total_bet_score": "500",//总投注额
            "valid_bet_score_total": "500",//总有效投注额
            "total_win_score": "0"//总派彩金额
        },
        "all_total_score": {//总计
        "total_bet_score": "500",//总投注额
        "valid_bet_score_total": "500",//总有效投注额
        "total_win_score": "0"//总派彩金额
        }
    }
    }
     */
    public function userChartInfo(Request $request)
    {
        $user_id = $request->input('user_id');
        $account = $request->input('account');
        $game_hall_id = $request->input('game_hall_id');
        $round_no = $request->input('round_no');
        $status = $request->input('status');
        $start_add_time = $request->input('start_add_time');
        $end_add_time = $request->input('end_add_time');
        $page_num = $request->input('page_num',10);
        $is_page = $request->input('is_page',1);
        $game_period = $request->input('game_period');
        $connect_mode = $request->input('connect_mode');//扣费模式
        $field = [
            '_id',
            'account',
            'total_bet_score',
            'total_win_score',
            'valid_bet_score_total',
            'start_time','end_time',
            'server_name',
            'is_cancel',
            'round_no',
            'game_period',
            'dwRound',
            'remark',
            'is_mark',
            'game_hall_code',
            'game_name',
            'cat_id',
            'ip_info',
            'game_result',
            'connect_mode'
        ];
        $match = [];
        $db = UserChartInfo::select($field);

        //获取测试，联调代理id
        $ids = Agent::where(['grade_id' => 2, 'is_hall_sub' => 0])->whereIn('account_type',[2,3])->pluck('id')->toArray();

        $db->whereNotIn('agent_id', $ids);

        $match['agent_id']['$nin'] = $ids;

        if(isset($connect_mode) && $connect_mode !== '') {
            $connect_mode = (int)$connect_mode;
            if($connect_mode == 1) {
                $db->where('connect_mode',1);
                $match['connect_mode'] = 1;
            } else {
                $db->where('connect_mode','<>', 1);
                $match['connect_mode']['$ne'] = 1;
            }

        }

        if(isset($account) && !empty($account)) {
            $db->where('account',$account);
            $match['account'] = $account;
        }

        if(isset($game_period) && !empty($game_period)) {
            $db->where('game_period',$game_period);
            $match['game_period'] = $game_period;
        }

        if(isset($user_id) && !empty($user_id)) {

            $db->where('user_id',(int)$user_id);
            $match['user_id'] = (int)$user_id;
        }

        if(isset($game_hall_id) && $game_hall_id !== '') {

            $db->where('game_hall_id',(int)$game_hall_id);
            $match['game_hall_id'] = (int)$game_hall_id;
        }

        if(isset($round_no) && !empty($round_no)) {

            $db->where('round_no',$round_no);
            $match['round_no'] = $round_no;
        }

        if(isset($status) && !empty($status)) {
            switch ($status) {
                //未取消未派彩
                case 1:
                    $db->where('is_cancel',0);
                    $db->where('is_mark',0);
                    $match['is_cancel'] = 0;
                    $match['is_mark'] = 0;
                    break;
                //未取消已派彩
                case 2:
                    $db->where('is_cancel',0);
                    $db->where('is_mark',1);
                    $match['is_cancel'] = 0;
                    $match['is_mark'] = 1;
                    break;
                //已取消未派彩
                case 3:
                    $db->where('is_cancel',1);
                    $db->where('is_mark',0);
                    $match['is_cancel'] = 1;
                    $match['is_mark'] = 0;
                    break;
                //已取消已派彩
                case 4:
                    $db->where('is_cancel',1);
                    $db->where('is_mark',1);
                    $match['is_cancel'] = 1;
                    $match['is_mark'] = 1;
                    break;
            }
        }

        if(isset($start_add_time) && !empty($start_add_time)) {
            $s_time = Carbon::parse($start_add_time)->timestamp;
            $start_add_time = new \MongoDB\BSON\UTCDateTime($s_time * 1000);
            $db->where('start_time', '>=', $start_add_time);
            $match['start_time']['$gte'] = $start_add_time;
        }

        if(isset($end_add_time) && !empty($end_add_time)) {
            $e_time = Carbon::parse($end_add_time)->timestamp + 1;
            $end_add_time = new \MongoDB\BSON\UTCDateTime($e_time * 1000);
            $db->where('start_time', '<', $end_add_time);
            $match['start_time']['$lt'] = $end_add_time;

        }

        $db->orderby('start_time','desc');
        //总计
        $all_total_score = [
            'total_bet_score' => 0,
            'valid_bet_score_total' => 0,
            'total_win_score' => 0,
        ];

        if($is_page) {
            //总计处理
            $group = ['is_cancel'=>'$is_cancel'];
            $field = ['total_bet_score'=>1, 'valid_bet_score_total'=>1, 'total_win_score'=>1, '_id'=>0];
            $match['is_cancel'] = 0;
            $total_data = GameStatisticsController::getUserChartInfo($group,$match,$field);

            if( isset($total_data[0])) {
                $total_data = $total_data[0];
                $all_total_score['total_bet_score'] = number_format($total_data['total_bet_score'], 2);
                $all_total_score['valid_bet_score_total'] = number_format($total_data['valid_bet_score_total'], 2);
                $all_total_score['total_win_score'] = number_format($total_data['total_win_score'], 2);
            }
            $re = $db->paginate((int)$page_num);
        } else {
            $re = $db->get();
        }
        //每页小计
        $total_score = [
            'total_bet_score' => 0,
            'valid_bet_score_total' => 0,
            'total_win_score' => 0,
        ];
        foreach ($re as &$v){
            $v['connect_mode'] = isset($v['connect_mode']) ? $v['connect_mode'] : 0;//添加扣费模式字段
            $v->start_time = $v->start_time->__tostring();
            $v->start_time = date('Y-m-d H:i:s',$v->start_time/1000);
            !$v->is_cancel && $total_score['total_bet_score'] += $v->total_bet_score;

            if( $v->end_time ) {
                $v->end_time = $v->end_time->__tostring();
                $v->end_time = date('Y-m-d H:i:s',$v->end_time/1000);
            }

            !$v->is_cancel && $total_score['valid_bet_score_total'] += $v->valid_bet_score_total;
            !$v->is_cancel && $total_score['total_win_score'] += $v->total_win_score;

            $v->total_bet_score = number_format($v->total_bet_score, 2);
            $v->valid_bet_score_total = number_format($v->valid_bet_score_total, 2);
            $v->total_win_score = number_format($v->total_win_score, 2);
            (time() - strtotime($v->start_time)) > 60 && $v->is_mark != 1 && $v->is_cancel != 1  ? $v->is_rollback = 1 : $v->is_rollback = 0; //判断注单是否异常(一分钟还未派彩则为异常数据)
        }
        unset($v);

        if( count($total_score) ) {
            $total_score['total_bet_score'] = number_format($total_score['total_bet_score'],2);
            $total_score['valid_bet_score_total'] = number_format($total_score['valid_bet_score_total'],2);
            $total_score['total_win_score'] = number_format($total_score['total_win_score'],2);
        }

        if( $is_page ) {
            $re = $re->toArray();
        } else {
            $re = ['data' => $re];
            $all_total_score = $total_score;
        }
        $re['total_score'] = $total_score;
        $re['all_total_score'] = $all_total_score;
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$re,
        ]);
    }

    //此接口弃用
    public function showOrder($_id)
    {
        return '此接口弃用';
        $data = PlayerOrder::find($_id);

        if($data) {
            $data->add_time = date('Y-m-d H:i:s',$data->add_time->__tostring()/1000);
            $data->betarea_code = config('betarea.'.$data->cat_id.'.'.$data->bet_type)['betarea_code'];
            $data->bet_money = number_format($data->bet_money, 2);
            $data->bet_money_valid = number_format($data->bet_money_valid, 2);
            $data->payout_win = number_format($data->payout_win, 2);
            $data->odds = number_format($data->odds, 2);
        }
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$data,
        ]);
    }

    /**
     * @api {get} /player/order/{account}/{round_no} 查看注单详情结果
     * @apiDescription 查看注单详情结果 account：玩家登录名，round_no:局ID
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "round_no": "f20b7f4872e61148",//局id
    "server_name": "17",//桌号
    "add_time": "2017-07-05 01:52:49",//下注时间
    "remark": "23",//牌信息
    "cat_id": 2,//游戏分类ID
    "game_result": "23",//游戏结果
    "game_period": "876-104",//靴+局
    "total": {
    "bet_money": "1,800.00",//总下注金额
    "bet_money_valid": "1,800.00",//总有效下注金额
    "payout_win": "-1,800.00"//总派彩金额
    },
    "data": [
    {
    "odds": 36,//赔率
    "bet_money": "200.00",//下注金额
    "bet_money_valid": "200.00",//有效下注金额
    "payout_win": "-200.00",//派彩金额
    "bet_type": 157,//下注类型
    "cat_id": 2//游戏分类ID
    "game_hall_id": 2//游戏厅ID
    "game_id": 2//游戏ID
    }
    ]
    }
    }
     */
    public function showOrderDetail(string $account, string $round_no)
    {
        $bet_field = [
            'odds',
            'payout_win',
            'bet_money',
            'bet_money_valid',
            'bet_type',
            'cat_id',
            'game_hall_id',
            'game_id',
        ];
        $info_field = [
            'round_no',
            'server_name',
            'start_time',
            'game_result',
            'remark',
            'game_period',
            'cat_id',
        ];
        $info = UserChartInfo::select($info_field)->where('account', $account)->where('round_no', $round_no)->first();
        $info->add_time = date('Y-m-d H:i:s',$info->start_time->__tostring()/1000);
        unset($info->_id,$info->start_time);

        $datas = PlayerOrder::select($bet_field)->where('user_name', $account)->where('round_no', $round_no)->get();
        $total = [
            'bet_money' => 0,
            'bet_money_valid' => 0,
            'payout_win' => 0,
        ];

        foreach ($datas as $v){
            //统计金额
            $total['bet_money'] += $v->bet_money;
            $total['bet_money_valid'] += $v->bet_money_valid;
            $total['payout_win'] += $v->payout_win;

            //下注区域列表
            $v->bet_money = number_format($v->bet_money, 2);
            $v->bet_money_valid = number_format($v->bet_money_valid, 2);
            $v->payout_win = number_format($v->payout_win, 2);
            $v->odds = number_format($v->odds, 2);
            unset($v->_id);
        }

        $total['bet_money'] = number_format($total['bet_money'], 2);
        $total['bet_money_valid'] = number_format($total['bet_money_valid'], 2);
        $total['payout_win'] = number_format($total['payout_win'], 2);

        $info['data'] = $datas;
        $info['total'] = $total;

        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$info,
        ]);
    }

    /**
     * @api {post} /player/order/rollbackOrder （无效接口）注单回滚
     * @apiDescription 注单回滚
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} _id 注单数据 _id
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function rollbackOrder($data)
    {

        if((time()-$data->add_time->__tostring()/1000) < 60)
        {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('order203.not_payout'),
                'result'=>'',
            ]);
        }

        //检查当前注单是否已经派彩，如果已经派彩则不能进行回滚操作
        if($data->calculated == 1)
        {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('order203.order_not_calculated'),
                'result'=>'',
            ]);
        }
        //检查订单是否已经取消，已经取消的不能进行回滚操作
        if($data->is_cancel == 1)
        {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('order203.order_is_cancel'),
                'result'=>'',
            ]);
        }

        //获取用户的信息
        $userInfo = DB::table('lb_user')->select('money')->where(['uid'=>(int)$data->user_id])->first();
        if(!$userInfo)
        {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('order203.user_not_exist'),
                'result'=>'',
            ]);
        }
        //进行数据回滚操作（增加多一条现金记录表数据，同时修改order表对应记录的状态=>is_cancel 修改为1）
        PlayerOrder::rollbackOrder($data);

        return $this->response->array([
            'code'=>0,
            'text'=> trans('order203.success'),
            'result'=>''
        ]);
    }

    /**
     * @api {post} /player/order/cancelOrder (无效接口)注单取消（单局+单用户）
     * @apiDescription 注单取消
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} round_no 游戏局号 round_no
     * @apiParam {String} user_name 用户登录名
     * @apiParam {String} desc 备注信息 desc
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function cancelPay(Request $request)
    {
        $round_no = $request->input('round_no');
        $user_name = $request->input('user_name');
        $desc = $request->input('desc');
        if(!$round_no || !$user_name)
        {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('order203.id_not_exist'),
                'result'=>'',
            ]);
        }

        //判断注单是否存在
        $data = UserChartInfo::where(['round_no'=>$round_no])->first();
        if(!$data)
        {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('order203.order_not_exist'),
                'result'=>'',
            ]);
        }

        //检查订单是否已经取消，已经取消的不能进行取消操作
        if($data->is_cancel == 1)
        {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('order203.order_is_cancel'),
                'result'=>'',
            ]);
        }
        //获取该局，该用户下的注单信息
        $orderList = PlayerOrder::where(['round_no'=>$round_no,'account'=>$user_name])->get()->toArray();
        //判断注单是否已经派彩，没有派彩的记录则进行注单回滚操作
        if($data->is_mark != 1)
        {
            foreach ($orderList as $key=>$val)
            {
                $this->rollbackOrder($val);
            }
            exit();
        }

        /**进行正常取消操作*/
        foreach ($orderList as $key=>$val)
        {
            $res = PlayerOrder::cancelOrder($val,$desc);
        }

        if($res)
        {
            return $this->response()->array([
                'code'  => 0,
                'text'  => trans('order203.success'),
                'result'    => ''
            ]);
        }
        else
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('order203.fails'),
                'result'   => ''
            ]);
        }
    }

    /**
     * @api {post} /player/order/bulkCancelOrder 注单取消（一键取消+单条取消）
     * @apiDescription 注单取消
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} round_no 局号
     * @apiParam {String} user_name 用户名
     * @apiParam {String} desc 备注信息 desc
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function  bulkCancelPay(Request $request)
    {
        $round_no = $request->input('round_no');
        $desc = $request->input('desc');
        $user_name = $request->input('user_name');
        if(!$round_no)
        {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('order203.id_not_exist'),
                'result'=>'',
            ]);
        }
        $state = true;//是否执行本地取消操作的标识符，当包网返回失败时会修改为false,代表不可以执行本地取消操作
        $nowTime = time();//防止因为操作时间不统一，使用该变量为操作时间
        $b_user_name = "";

        //判断是否需要通知包网
        $findWhere = [
            'round_no'=>$round_no,
            'is_cancel'=>0,
            'connect_mode' => 1,
        ];
        if($user_name)
        {
            $findWhere['account'] = $user_name;
            //获取用户所属的代理商code
            $agentCode = DB::table("lb_user")->where(['lb_user.user_name'=>decrypt_($user_name)])->select('au.agent_code')->leftJoin("lb_agent_user as au",function($join){
                $join->on('lb_user.agent_id','=','au.id');
            })->first();
            if($agentCode->agent_code){
                $b_user_name = str_replace($agentCode->agent_code,"",$user_name);
            }
        }
        //获取已经开通共享钱包的厅主ID
        $hallIdList = Agent::where(["connect_mode"=>1])->select("id")->get()->toArray();
        $hallIdList = count($hallIdList) >0 ? array_column($hallIdList,"id") : [];

        //获取到满足条件的派彩信息
        $data = UserChartInfo::where($findWhere)->select("hall_id")->groupBy("hall_id")->get()->toArray();
        $charHallIdList = count($data) > 0 ? array_column($data,"hall_id") : [];
        if(count($hallIdList) > 0 && count($charHallIdList) > 0)
        {
            foreach ($charHallIdList as $val)
            {
                if(in_array($val,$hallIdList))//满足条件，当前取消的注单中有属于开通了共享钱包模式的厅主
                {
                    //通知包网平台
                    $msgData['user_name'] = $b_user_name;
                    $msgData['round_no'] = $round_no;
                    $msgData['rollback_time'] = date("Y-m-d H:i:s",$nowTime);
                    $msgData['rollback_remark'] = $desc;
                    $roundot = new GameServerController();
                    $state = $roundot->roundotUserBalanceMessage($msgData,1014);
                    if($state) continue;
                }
            }
        }

        //包网返回失败，则不进行本地取消操作
        if(!$state)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('order203.fails'),
                'result'    => ''
            ]);
        }


        //开始进行本地正常的取消注单操作
        $where = [
            'round_no'=>$round_no
        ];
        if($user_name)
        {
            $where['account'] = $user_name;
        }

        //获取到满足条件的派彩信息
        $data = UserChartInfo::where($where)->get();
        //并发防止同时取消操作

        $redis = Redis::connection("default");

        if($redis->get($round_no))
        {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('order203.order_is_canceling'),
                'result'=>'',
            ]);
        }
        $redis->set($round_no,'is_lock');

        if($data)
        {
            foreach ($data as $key=>$val)
            {
                if($val->is_cancel == 1)
                {
                    continue;//已经取消过的不给予取消
                }
                //判断是进行取消还是进行回滚操作
                if($val->is_mark == 1)
                {//已经派彩则进行取消操作
                    PlayerOrder::cancelOrder($val,$desc,$nowTime);
                }
                else if($val->is_mark == 0 && $val->is_cancel == 0 &&(time() - ($val->start_time->__toString()/1000) > 60))
                {//没有派彩，也没有取消，并且时间超过60秒的进行回滚操作
                    PlayerOrder::rollbackOrder($val,$desc,$nowTime);
                }
            }
        }


        $redis->del($round_no);
        return $this->response()->array([
            'code'  => 0,
            'text'  => trans('order203.success'),
            'result'    => ''
        ]);

    }


    //根据游戏厅ID获取到对应的厅级别类型
    private function getHallTypeById($hall_id)
    {
        $type = 0;
        switch ($hall_id)
        {
            case 0:
                $type = 32; //旗舰厅取消退回
                break;
            case 1:
                $type = 35; //贵宾厅取消退回
                break;
            case 2:
                $type = 33; //金臂厅取消退回
                break;
            case 3:
                $type = 34; //至尊厅取消退回
                break;
            default :
                $type = 21;
        }

        return $type;
    }

    /**
     * @api {get} /player/{user_id}/getUserBalance 查询玩家余额（共享钱包）
     * @apiDescription 查询玩家余额（共享钱包）
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} locale 语言
     * @ apiSampleRequest http://app-loc.dev/api/player/191/balance
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "data": {
    "balance": 54172
    }
    }
    }
     */
    public function getUserBalance(Request $request,$user_id)
    {
        if(!$user_id)
        {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.param_error'),
                'result'=>'',
            ]);
        }

        //判断该用户所属的厅主是否有开通共享钱包
        $userInfo = DB::table("lb_user")->where(["uid"=>$user_id])->first();
        $hall_id = $userInfo->hall_id;
        $hallInfo = DB::table("lb_agent_user")->where(['id'=>$hall_id,'is_hall_sub'=>0,"grade_id"=>1])->first();
        $agentInfo = DB::table("lb_agent_user")->where(['id'=>$userInfo->agent_id,"grade_id"=>2])->first();

        if(!$hallInfo || !isset($hallInfo->connect_mode))
        {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.param_error'),
                'result'=>'',
            ]);
        }

        //如果用户所属的厅主不是共享钱包模式则直接返回用户的余额
        if($hallInfo->connect_mode != 1)
        {
            return $this->response->array([
                'code'=>0,
                'text'=>trans('agent.success'),
                'result'=>["data"=>[
                    "balance" => $userInfo->money
                ]],
            ]);
        }
        
        //如果用户所属的厅主为共享钱包模式则进调用包网获取用户余额
        $data["agent_id"] = $userInfo->agent_id;
        $data["agent_name"]  =  $userInfo->agent_name;
        $data["user_id"] = $userInfo->uid;
        $data["user_name"] = str_replace($agentInfo->agent_code,"",encrypt_($userInfo->user_name));
        $data["oper_code"] = 2;
        $server = new GameServerController();
        $res = $server->roundotUserBalanceMessage($data,1002);
        if($res)
        {
            return $this->response->array([
                'code'=>0,
                'text'=>trans('agent.success'),
                'result'=>["data"=>[
                    "balance" => $res['user_balance']
                ]],
            ]);
        }
        else
        {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.fails'),
                'result'=>'',
            ]);
        }

    }
}