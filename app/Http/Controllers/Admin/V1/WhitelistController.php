<?php
namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Models\Whitelist;
use App\Models\Agent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

/**
 * Class WhitelistController
 * @package App\Http\Controllers\Admin\V1
 * @desc 白名单管理
 */
class WhitelistController extends BaseController
{
    public function __construct()
    {
    }

    /**
     * @api {get} /whitelist 白名单列表
     * @apiDescription 白名单列表
     * @apiGroup whitelist
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} agent_name  运营商名称
     * @apiParam {String} ip  运营商ip地址
     * @apiParam {Number} state 状态：1可用，0不可用
     * @apiParam {Number} page 当前页 默认为1
     * @apiParam {Number} page_num 每页显示条数 默认 10
     * @apiParam {Number} is_page 是否分页 1是，0否，默认1
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
            {
                "code": 0,//状态码，0：成功，非0：错误
                "text": "操作成功",//文本描述
                "result": {//结果对象
                "total": 2,//总条数
                "per_page": 10,//每页显示条数
                "current_page": 1,//当前页
                "last_page": 1,//上一页
                "next_page_url": null,//下一页url
                "prev_page_url": null,//前一页url
                "data": [//数据对象
                    {
                    "id": 1,//白名单id
                    "ip_info": "127.0.0.1,220.95.210.87,192.168.31.28,192.168.31.60,192.168.31.58",//ip地址集
                    "agent_id": "2",//代理商id
                    "agent_name": "agent_test",//代理商名称
                    "agent_domain": "http://www.tt8828.com/Lebo/game.php",//代理域名
                    "agent_seckey": "7a16812ee6cf273798fb392356ff0d8ae0226a55",//秘钥
                    "seckey_exp_date": "2017-04-07 21:48:40",//秘钥过期时间
                    "state": "1",//状态：1可用，0不可用
                    "agent_code": "c112"//代理商code，暂时用不到
                    }
                ]
                }
            }
     * @apiErrorExample {json} Error-Response:
            HTTP/1.1 200 OK
            {
                "code": 400,
                "text": "",
                "result": ""
            }
     */
    public function index(Request $request)
    {

        $agent_name = $request->input('agent_name');
        $ip = $request->input('ip');
        $state = $request->input('state');
        $page_num = $request->input('page_num', 10);
        $is_page = $request->input('is_page', 1);

        $obj = Whitelist::select('id','ip_info','agent_id','agent_name','agent_domain','seckey_exp_date','state','agent_code');

        if(isset($agent_name) && !empty($agent_name)) {
            $obj->where('agent_name', 'like', '%'.$agent_name.'%');
        }

        if(isset($ip) && $ip !== '') {
            $obj->whereRaw('FIND_IN_SET("'.$ip.'",ip_info)');
        }

        if(isset($state) && $state !== '') {
            $obj->where('state', $state);
        }
        $obj->orderby('id','desc');
        if($is_page) {
            $data = $obj->paginate($page_num);
        } else {
            $data['data'] = $obj->get();
        }

        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$data,
        ]);
    }

    /**
     * @api {post} /whitelist 添加白名单
     * @apiDescription 添加白名单列表
     * @apiGroup whitelist
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} agent_id  代理商ID
     * @apiParam {String} agent_domain  代理商域名 http://开头
     * @apiParam {String} ip_info  代理商IP  格式：127.0.0.1,128.0.0.2    PS: * 为所有Ip地址
     * @apiParam {Number} state 状态：1可用，0失效
     * @apiSuccessExample {json} Success-Response:
        HTTP/1.1 200 OK
        {
            "code": 0,
            "text": "操作成功",
            "result": ""
        }
     * @apiErrorExample {json} Error-Response:
        HTTP/1.1 200 OK
        {
            "code": 400,
            "text": "代理商不存在",
            "result": ""
        }
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'agent_id' => 'required|unique:white_list,agent_id',
//            'agent_domain' => 'required',
            'ip_info' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }
        $agent_id = $request->input('agent_id');
        $agent_domain = $request->input('agent_domain');
        $ip_info = $request->input('ip_info');

        $agent = Agent::where(['id' => $agent_id, 'grade_id' => 1])->select('id','user_name')->first();
        if( ! $agent ) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.hall_not_exist'),//厅主不存在
                'result'=>'',
            ]);
        }

        if( $agent_domain && !filter_var($agent_domain,FILTER_VALIDATE_URL) ) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.domain_error'),
                'result'=>'',
            ]);
        }

        if($ip_info != '*') {
            foreach (explode(',', $ip_info) as $ip) {
                if( !filter_var($ip,FILTER_VALIDATE_IP) ) {

                    return $this->response->array([
                        'code'=>400,
                        'text'=> trans('agent.ip_error'),
                        'result'=>'',
                    ]);

                }
            }
        }


        $attributes = $request->except('token','locale');
        $attributes['agent_name'] = $agent->user_name;

        $str = str_shuffle($agent->user_name.mt_rand(10,100000));
        $securityKey = createSecurityKey(env('SECURITY_KEY_ENCRYPT'),$str);

        $attributes['agent_seckey'] = $securityKey;
        $attributes['seckey_exp_date'] = Carbon::parse('+'.env('KEY_MAX_VALID_TIME').' days')->toDateTimeString();

        $re = Whitelist::create($attributes);

        if ($re) {

            @addLog(['action_name'=>'添加白名单','action_desc'=>' 添加了一个白名单，所属代理商为：'.$agent->user_name,'action_passivity'=>$agent->user_name]);

            //把代理商白名单信息和agent_code
            self::storeAgentWhitelist($agent_id);

            return $this->response->array([
                'code'=>0,
                'text'=> trans('agent.success'),
                'result'=>'',
            ]);
        } else {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.add_fails'),
                'result'=>'',
            ]);
        }
    }

    /**
     * @api {put} /whitelist/{id} 保存白名单
     * @apiDescription 保存白名单
     * @apiGroup whitelist
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} agent_id  代理商ID
     * @apiParam {String} agent_domain  代理商域名
     * @apiParam {String} ip_info  代理商IP  格式：127.0.0.1,128.0.0.2
     * @apiParam {Number} state 状态：1可用，0失效
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
        {
            "code": 0,
            "text": "",
            "result": ""
        }
     * @apiErrorExample {json} Error-Response:
    HTTP/1.1 200 OK
        {
            "code": 400,
            "text": "",
            "result": ""
        }
     */
    public function update(Request $request, $id)
    {
        $validator = \Validator::make($request->input(), [
            'agent_id' => 'required_with:agent_id|unique:white_list,agent_id,'.$id,
//            'agent_domain' => 'required',
            'ip_info' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }
        $agent_id = $request->input('agent_id');
        $agent_domain = $request->input('agent_domain');
        $ip_info = $request->input('ip_info');

        $whitelist = Whitelist::find($id);
        if( !$whitelist ) {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.whitelist_not_exist'),
                'result'=>'',
            ]);
        }

        $agent = Agent::where(['id' =>$agent_id ,'grade_id' => 1])->first();
        if( !$agent ) {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.hall_not_exist'),//厅主不存在
                'result'=>'',
            ]);
        }

        if($agent_domain && !filter_var($agent_domain,FILTER_VALIDATE_URL) ) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.domain_error'),
                'result'=>'',
            ]);
        }

        if($ip_info != '*') {
            foreach (explode(',', $ip_info) as $ip) {
                if (!filter_var($ip, FILTER_VALIDATE_IP)) {

                    return $this->response->array([
                        'code' => 400,
                        'text' => trans('agent.ip_error'),
                        'result' => '',
                    ]);

                }
            }
        }

        $attributes = $request->except('token','locale');
        if($whitelist->agent_id != $agent->id) {

            $attributes['agent_name'] = $agent->id;
            $attributes['agent_name'] = $agent->user_name;

            $user_name =  $agent->user_name;
        } else {
            $user_name =  $whitelist->agent_name;
        }

        if( $whitelist->seckey_exp_date <=  Carbon::now()->toDateTimeString() ) {
            //重新生成key
            $str = str_shuffle($user_name.mt_rand(10,100000));
            $securityKey = createSecurityKey(env('SECURITY_KEY_ENCRYPT'),$str);

            $attributes['agent_seckey'] = $securityKey;
            $attributes['seckey_exp_date'] = Carbon::parse('+'.env('KEY_MAX_VALID_TIME').' days')->toDateTimeString();
        }


        $re = $whitelist->update($attributes);

        if($re) {

            //把代理商白名单信息和agent_code
            self::storeAgentWhitelist($agent_id);

            @addLog(['action_name'=>'添加白名单','action_desc'=>' 修改了一个白名单信息，所属代理商为：'.$agent->user_name,'action_passivity'=>$agent->user_name]);



            return $this->response->array([
                'code'=>0,
                'text'=> trans('agent.success'),
                'result'=>'',
            ]);
        } else {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.save_fails'),
                'result'=>'',
            ]);
        }
    }


    /**
     * @api {get} /whitelist/{id} 获取单条白名单
     * @apiDescription 获取单条白名单
     * @apiGroup whitelist
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
            {
                "code": 0,//状态码，0：成功，非0：错误
                "text": "操作成功",//文本描述
                "result": {//结果对象
                    "id": 4,//白名单id
                    "ip_info": "127.0.0.1,192.168.0.1",//ip地址集
                    "agent_id": "10",//代理商id
                    "agent_name": "hongbo",//代理商名称
                    "agent_domain": "http://baidu.com",//代理域名
                    "state": "1",//状态：1可用，0不可用
                    "agent_code": ""//代理商code，暂时用不到
                }
            }
     */
    public function show(Request $request, $id)
    {
        $data = Whitelist::select('id','ip_info','agent_id','agent_name','agent_domain','state','agent_code')->find($id);
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$data ? $data : '',
        ]);
    }

    /**
     * @api {delete} /whitelist/{id} 删除白名单
     * @apiDescription 删除白名单
     * @apiGroup whitelist
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
        {
            "code": 0,
            "text": "操作成功",
            "result": ""
        }
     * @apiErrorExample {json} Error-Response:
        HTTP/1.1 200 OK
        {
            "code": 400,
            "text": "操作失败",
            "result": ""
        }
     */
    public function delete(Request $request, $id)
    {
        $whitelist = Whitelist::select('id','agent_id','agent_name')->find($id);

        if( !$whitelist) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.whitelist_not_exist'),
                'result'=>'',
            ]);
        }

        $re = Whitelist::where('id', $id)->delete();
        if($re) {

            //把代理商白名单信息和agent_code
            self::storeAgentWhitelist($whitelist["agent_id"]);

            @addLog(['action_name'=>'删除白名单','action_desc'=>' 删除了一个白名单，所属代理商为：'.$whitelist->agent_name,'action_passivity'=>$whitelist->agent_name]);

            return $this->response->array([
                'code'=>0,
                'text'=> trans('agent.success'),
                'result'=>'',
            ]);
        } else {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.fails'),
                'result'=>'',
            ]);
        }
    }

    /**
     * @api {get} /whitelist/showKey/{id} 获取白名单秘钥
     * @apiDescription 获取白名单秘钥
     * @apiGroup whitelist
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,//状态码，0：成功，非0：错误
    "text": "操作成功",//文本描述
    "result": {//结果对象
    "agent_seckey": "f1d206d6b3f3a1e8851ba0cb9ee5edf2539c18a0",//秘钥
    }
    }
     */
    public function showKey(Request $request,$id)
    {
        $data = Whitelist::select('agent_seckey')->find($id);
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$data ? $data : '',
        ]);
    }

    //存储代理商agent_code和对应的厅主白名单信息
    public static function storeAgentWhitelist($hall_id=0, $agent_id=0){


        if( !$hall_id) {
            return false;
        }
        //获取厅主白名单信息
        $whiteInfo = Whitelist::where('agent_id',$hall_id)->where("state",1)->first();
        if( $whiteInfo ) {
            $whiteInfo = StringShiftToInt($whiteInfo->toArray(), ['agent_id','state']);
        }

        $where = [
            "grade_id" => 2,
            "is_hall_sub" => 0,
            "parent_id" => $hall_id,
        ];

        $agent_id && $where['id'] = $agent_id;

        $redis_name = "agentWhitelist";

        //获取厅主信息
        $hallAgent = Agent::select('id','user_name','connect_mode')->where(['id' => $hall_id,'grade_id' => 1, 'is_hall_sub' => 0])->first();

        //获取厅主下面代理商
        $agentInfo = Agent::select("id","user_name","agent_code","account_state","account_lock","account_type")->where($where)->get()->toArray();

        if ( ! $agentInfo) {
            return false;
        }
        $data = [];

        $redis = Redis::connection("default");

        foreach (StringShiftToInt($agentInfo,["id","account_state","account_lock","account_type"]) as $k => $v){
            if ($whiteInfo) {

                if ($v["account_state"] == 1){
                    $whiteInfo["agent_code"] = $v["agent_code"];//代理商code
                    $whiteInfo["account_type"] = $v["account_type"];//账号种类,1为正常账号,2为测试账号，3为调试账号
                    $whiteInfo["agent_id2"] = $v["id"];//代理商id
                    $whiteInfo["account_lock"] = $v["account_lock"];//代理商锁定状态
                    $whiteInfo['connect_mode'] = (int)$hallAgent['connect_mode'];//厅主的扣费模式
                    $data[$v["user_name"]] = json_encode($whiteInfo);
                } else {
                    //删除不正常的代理商
                    if ($redis->hexists($redis_name,$v["user_name"])) {
                        $redis->hdel($redis_name,$v["user_name"]);
                    }
                }
            } else {
                //删除不正常的代理商
                if ($redis->hexists($redis_name,$v["user_name"])) {
                    $redis->hdel($redis_name,$v["user_name"]);
                }
            }


        }
        $data && $redis->hmset($redis_name,$data);
        return true;
    }
}