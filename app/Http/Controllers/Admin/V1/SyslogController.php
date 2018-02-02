<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/4/10
 * Time: 14:46
 * 系统日志控制器
 */

namespace App\Http\Controllers\Admin\V1;


use App\Models\Apilog;
use App\Models\SysLog;
use App\Models\LoginLog;
use App\Models\DebugAccount;
use App\Models\ExceptionCashLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Agent;
use App\Models\UserChartInfo;
use Carbon\Carbon;

class SyslogController extends BaseController
{
    /**
     * @api {get} /syslog 查看系统日志列表
     * @apiDescription 查看系统日志列表
     * @apiGroup log
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiParam {String} user_name 用户名
     * @apiParam {String} action_name 操作类型
     * @apiParam {String} action_passivity 被操作对象
     * @apiParam {Date} start_date 开始时间
     * @apiParam {Date} end_date 结束时间
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 5,
    "per_page": 10,
    "current_page": 1,
    "last_page": 1,
    "next_page_url": null,
    "prev_page_url": null,
    "from": 1,
    "to": 5,
    "data": [
    {
    "id": 1,
    "action_name": "获取验证码", //执行的动作
    "user_id": 2, //操作账号ID
    "action_user": "agent", //操作账号
    "action_date": "2017-04-10 14:02:10", //操作时间
    "ip_info": "192.168.28.223" //操作IP
    },

    ]
    }
    }
     */
    public function index(Request $request)
    {
        $user_name = $request->input('user_name');
        $action_name = $request->input('action_name');
        $action_passivity = $request->input('action_passivity');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $page_num = (int)$request->input('page_num',10);
        $is_page = $request->input('is_page', 1);

        $db = SysLog::select();
        
        if(!empty($user_name))
        {
            $db->where('action_user','like','%'.$user_name.'%');
        }
        if(!empty($action_name))
        {
            $db->where('action_name','like','%'.$action_name.'%');
        }
        if(!empty($action_passivity))
        {
            $db->where('action_passivity','like','%'.$action_passivity.'%');
        }

        if(!empty($start_date))
        {
            $db->where('action_date','>=',$start_date);
        }

        if(!empty($end_date) && strtotime($start_date) < strtotime($end_date))
        {
            $db->where('action_date','<=',$end_date);
        }

        $db->orderBy('action_date','desc');
        if(!$is_page) {
            $res = $db->get()->toArray();
        } else {
            $res = $db->paginate($page_num)->toArray();
        }

        if(!$res['data'])
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('delivery.empty_list'),
                'result'        => ''
            ]);
        }

        return  $this->response()->array([
            'code'          => 0,
            'text'          => trans('delivery.success'),
            'result'        => $res
        ]);
    }

    /**
     * @api {get} /apilog 查看API调用日志
     * @apiDescription 查看API调用日志
     * @apiGroup log
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiParam {Date} start_date 开始时间
     * @apiParam {Date} end_date 结束时间
     * @apiParam {String} user_name 用户名
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 15,
    "per_page": 1,
    "current_page": 1,
    "last_page": 15,
    "next_page_url": "http://platform.dev/api/apilog?page=2",
    "prev_page_url": null,
    "from": 1,
    "to": 1,
    "data": [
    {
    "start_time": "2017-04-10 17:40:34", //开始时间
    "agent": "agent_test", //代理商
    "postData": "{\"s\":\"\\/deposit\",\"token\":\"2cc954dacab1d2948d635026cb587b1a669da494\",\"agent\":\"agent_test\",\"amount\":\"100\",\"username\":\"csj_play\"}", //提交参数
    "apiName": "会员充值", //接口业务名称
    "code": 0, //请求返回状态码
    "text": "Success", // 请求返回说明
    "result": "{\"order_sn\":\"LA410172344335845\",\"amount\":\"100.00\"}" ,// 请求返回数据
    "end_time": "2017-04-10 17:40:34" //接口结束时间
    },
    ]
    }
    }
     */
    public function apiLog(Request $request)
    {
        $agent = $request->input('user_name');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $page_num = (int)$request->input('page_num',10);
        $is_page = $request->input('is_page', 1);

        $db = Apilog::select(['user_name', 'apiName', 'end_time' ,'start_time', 'text', 'ip_info']);
        if(!empty($agent))
        {

            $db->where('user_name','like','%'.$agent.'%');
        }

        if(!empty($start_date))
        {
            $start_date = new \MongoDB\BSON\UTCDateTime(strtotime($start_date)* 1000);
            $db->where('start_time','>=',$start_date);
        }

        if(!empty($end_date) && strtotime($start_date) < strtotime($end_date))
        {
            $end_date = new \MongoDB\BSON\UTCDateTime(strtotime($end_date)* 1000);
            $db->where('start_time','<=',$end_date);
        }

        $db->orderby('start_time','desc');

        if(!$is_page) {
            $res = $db->get()->toArray();
        } else {
            $res = $db->paginate($page_num)->toArray();
        }

        if(!$res['data'])
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('delivery.empty_list'),
                'result'        => ''
            ]);
        }
        foreach ($res['data'] as $key=>$val)
        {
            if(isset($val['postData'])) unset($val['postData']);
            $val['start_time'] = $val['start_time']->__toString();
            $val['end_time'] = $val['end_time']->__toString();
            $res['data'][$key]['start_time'] = date('Y-m-d H:i:s',$val['start_time']/1000);
            $res['data'][$key]['end_time'] = date('Y-m-d H:i:s',$val['end_time']/1000);
        }

        return  $this->response()->array([
            'code'          => 0,
            'text'          => trans('delivery.success'),
            'result'        => $res
        ]);
    }

    /**
     * @api {get} /userLoginLog 查看API登录日志
     * @apiDescription 查看API登录日志
     * @apiGroup log
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiParam {Date} start_date 开始时间
     * @apiParam {Date} end_date 结束时间
     * @apiParam {String} user_name 用户名
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 15,
    "per_page": 1,
    "current_page": 1,
    "last_page": 15,
    "next_page_url": "http://platform.dev/api/userLoginLog?page=2",
    "prev_page_url": null,
    "from": 1,
    "to": 1,
    "data": [
    {
    "agent": "h88888",
    "postData": "{\"agent\":\"h88888\",\"token\":\"54c06a5c36d265938cbd8e00a805269dd53b3816\",\"username\":\"8Z998wAFdX\",\"login_type\":\"1\"}",
    "apiName": "玩家登录游戏",
    "ip_info": "192.168.29.83",
    "log_type": "login",
    "code": 0,
    "text": "Success",
    "result": "{\"url\":\"http:\\/\\/lebogame-pc-22.dev\\/game.php?uid=6b4e392cda61e701112bb\"}",
    "start_time": "2017-06-29 03:37:57",
    "end_time": "2017-06-29 03:37:57"
    },
    ]
    }
    }
     */
    public function UserLoginLog(Request $request)
    {

        $user_name = $request->input('user_name');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $page_num = (int)$request->input('page_num',10);
        $is_page = $request->input('is_page', 1);
        $db = DB::connection('mongodb')->collection('api_login_log');
        if(!empty($user_name))
        {
            $db->where('user_name','like','%'.$user_name.'%');
        }
        if(!empty($start_date))
        {
            $start_date = new \MongoDB\BSON\UTCDateTime(strtotime($start_date)* 1000);
            $db->where('start_time','>=',$start_date);
        }

        if(!empty($end_date) && strtotime($start_date) < strtotime($end_date))
        {
            $end_date = new \MongoDB\BSON\UTCDateTime(strtotime($end_date)* 1000);
            $db->where('end_time','<=',$end_date);
        }

        $db->orderBy('end_time','desc');
        if(!$is_page) {
            $res = $db->get()->toArray();
            $res = ['data' => $res];
        } else {
            $res = $db->paginate($page_num)->toArray();
        }

        if(empty($res))
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('delivery.empty_list'),
                'result'        => ''
            ]);
        }


        foreach ($res['data'] as $key=>$val)
        {
            $val['start_time'] = $val['start_time']->__toString();
            $val['end_time'] = $val['end_time']->__toString();
            $res['data'][$key]['start_time'] = date('Y-m-d H:i:s',$val['start_time']/1000);
            $res['data'][$key]['end_time'] = date('Y-m-d H:i:s',$val['end_time']/1000);
        }
        return  $this->response()->array([
            'code'          => 0,
            'text'          => trans('delivery.success'),
            'result'        => $res
        ]);
    }


    /**
     * @api {get} /playerLoginLog 查看玩家登录日志
     * @apiDescription 查看玩家登录日志
     * @apiGroup log
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiParam {Date} start_date 开始时间
     * @apiParam {Date} end_date 结束时间
     * @apiParam {String} user_name 用户名
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 15,
    "per_page": 1,
    "current_page": 1,
    "last_page": 15,
    "next_page_url": "http://platform.dev/api/playerLoginLog?page=2",
    "prev_page_url": null,
    "from": 1,
    "to": 1,
    "data": [
    {
    "agent": "h88888",
    "desc": "登入成功",
    "ip_info": "192.168.29.83",
    "device_type": "手机端",
    "user_name": "1111",
    "add_time": "2017-06-29 03:37:57",
    },
    ]
    }
    }
     */
    public function PlayerLoginLog(Request $request)
    {

        $user_name = $request->input('user_name');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $page_num = (int)$request->input('page_num',10);
        $is_page = $request->input('is_page', 1);

        $db = LoginLog::select(['user_name', 'agent_name', 'add_time' , 'device_type', 'desc', 'ip_info']);

        if(!empty($user_name))
        {
            $db->where('user_name','like','%'.$user_name.'%');
        }
        if(!empty($start_date))
        {
            $start_date = new \MongoDB\BSON\UTCDateTime(strtotime($start_date)* 1000);
            $db->where('add_time','>=',$start_date);
        }

        if(!empty($end_date) && strtotime($start_date) < strtotime($end_date))
        {
            $end_date = new \MongoDB\BSON\UTCDateTime(strtotime($end_date)* 1000);
            $db->where('add_time','<=',$end_date);
        }

        // 过滤联调账号


        $db->whereNotIn('hall_id',$this->debugHallInfo());


        $db->orderBy('add_time','desc');

        if(!$is_page) {
            $res = $db->get()->toArray();
            $res = ['data' => $res];
        } else {
            $res = $db->paginate($page_num)->toArray();
        }

        if(empty($res))
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('delivery.empty_list'),
                'result'        => ''
            ]);
        }

        foreach ($res['data'] as $key => $val)
        {
            $val['add_time'] = $val['add_time']->__toString();
            $res['data'][$key]['add_time'] = date('Y-m-d H:i:s',$val['add_time']/1000);
        }

        return  $this->response()->array([
            'code'          => 0,
            'text'          => trans('delivery.success'),
            'result'        => $res
        ]);
    }

    /**
     * @api {get} /agentOperationLog 查看厅主操作日志
     * @apiDescription 查看系统日志列表
     * @apiGroup log
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader{String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiParam {String} user_name 用户名
     * @apiParam {String} action_name 操作类型
     * @apiParam {String} action_passivity 被操作对象
     * @apiParam {Date} start_date 开始时间
     * @apiParam {Date} end_date 结束时间
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 3,
    "per_page": 50,
    "current_page": 1,
    "last_page": 1,
    "next_page_url": null,
    "prev_page_url": null,
    "from": 1,
    "to": 3,
    "data": [
    {
    "_id": {
    "$oid": "595c9ad93c1a2115e400143a"
    },
    "action_name": "修改子账号状态操作",
    "user_id": 454,
    "action_user": "gcg",
    "action_desc": "删除账号; 名称gaucnhaoguo ID458",
    "action_passivity": "代理商账号表",
    "action_date": "2017-07-05 03:52:57",
    "ip_info": "127.0.0.1"
    },
    {
    "_id": {
    "$oid": "595c9a7e3c1a2115e4001439"
    },
    "action_name": "修改子账号密码操作",
    "user_id": 454,
    "action_user": "gcg",
    "action_desc": "修改子账号密码操作; 名称 ID458",
    "action_passivity": "代理商账号表",
    "action_date": "2017-07-05 03:51:26",
    "ip_info": "127.0.0.1"
    },
    {
    "_id": {
    "$oid": "595c9a4e3c1a2115e4001438"
    },
    "action_name": " 编辑保持账户权限信息",
    "user_id": 454,
    "action_user": "gcg",
    "action_desc": " 编辑子账户权限信息; 名称gaucnhaoguo ID458",
    "action_passivity": "代理商账号表",
    "action_date": "2017-07-05 03:50:38",
    "ip_info": "127.0.0.1"
    }
    ]
    }
    }
     */
    public function AgentOperationLog(Request $request)
    {
        $user_name = $request->input('user_name');
        $action_name = $request->input('action_name');
        $action_passivity = $request->input('action_passivity');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $page_num = (int)$request->input('page_num',10);
        $is_page = $request->input('is_page', 1);
        $db = DB::connection('mongodb')->collection('agent_operation_log');
        if(!empty($user_name))
        {
            $db->where('action_user','like','%'.$user_name.'%');
        }
        if(!empty($action_name))
        {
            $db->where('action_name','like','%'.$action_name.'%');
        }
        if(!empty($action_passivity))
        {
            $db->where('action_passivity','like','%'.$action_passivity.'%');
        }

        if(!empty($start_date))
        {
            $db->where('action_date','>=',$start_date);
        }

        if(!empty($end_date) && strtotime($start_date) < strtotime($end_date))
        {
            $db->where('action_date','<=',$end_date);
        }
        $db->orderBy('action_date','desc');
        if(!$is_page) {
            $res = $db->get()->toArray();
        } else {
            $res = $db->paginate($page_num)->toArray();
        }

        if(empty($res))
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('delivery.empty_list'),
                'result'        => ''
            ]);
        }


        if(!$res['data'])
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('delivery.empty_list'),
                'result'        => ''
            ]);
        }

        return  $this->response()->array([
            'code'          => 0,
            'text'          => trans('delivery.success'),
            'result'        => $res
        ]);
    }


    /**
     * @api {get} /exception/cash/log 查看异常注单日志
     * @apiDescription 查看异常注单日志
     * @apiGroup log
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader{String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} cash_record_id 单号
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiParam {String} user_name 用户名
     * @apiParam {Number} uid 用户名id
     * @apiParam {String} round_no 局id
     * @apiParam {Date} start_date 开始时间
     * @apiParam {Date} end_date 结束时间
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
        "total": 3,
        "per_page": 10,
        "current_page": 1,
        "last_page": 1,
        "next_page_url": null,
        "prev_page_url": null,
        "from": 1,
        "to": 3,
        "data": [
            {
                "_id": "596ec2ed7a545126798e784e",
                "user_order_id": "59479f5ee138237b244ec4ae",//注单id
                "uid": 375179,//用户id
                "user_name": "a9TEST717929",//用户登录名
                "agent_id": 2,//代理商登id
                "agent_name": "agnet_test",//代理商登录名
                "hall_id": 1,//厅主id
                "hall_name": "csj",//厅主登录名
                "round_no": "71a45f7196da5d8e",//局id
                "payout_win": 500,//派彩金额
                "user_money": 500,//用户余额
                "bet_time": "2017-06-19 05:54:51",//下注时间
                "desc": "取消异常派彩",//备注
                "action_user": "a9TEST717929",//操作人
                "action_user_id": 375179,//操作人id
                "action_passivity": "下注明细",//操作对象
                "add_time": "2017-06-19 06:29:57"//添加时间（操作时间）
            }
        ]
        }
    }
     */
    public function ExceptionCashLog(Request $request)
    {
        $cash_record_id = $request->input('cash_record_id');
        $user_order_id = $request->input('user_order_id');
        $round_no = $request->input('round_no');
        $uid = $request->input('uid');
        $user_name = $request->input('user_name');
        $start_time = $request->input('start_date');
        $end_time = $request->input('end_date');
        $page_num = $request->input('page_num',10);

        $db = ExceptionCashLog::select();

        if( isset($cash_record_id) && !empty($cash_record_id) ) {
            $db->where('cash_record_id', $cash_record_id);
        }

        if( isset($user_order_id) && !empty($user_order_id) ) {
            $db->where('user_order_id', $user_order_id);
        }

        if( isset($round_no) && !empty($round_no) ) {
            $db->where('round_no', $round_no);
        }

        if( isset($uid) && !empty($uid) ) {
            $db->where('uid', (int) $uid);
        }

        if( isset($user_name) && !empty($user_name) ) {
            $db->where('user_name', $user_name);
        }

        if( isset($end_time) && !empty($start_time) ) {
            $s_time = Carbon::parse($start_time)->timestamp;
            $db->where('add_time', '>=',new \MongoDB\BSON\UTCDateTime($s_time * 1000));
        }

        if( isset($start_time) && !empty($end_time) ) {
            $e_time = Carbon::parse($end_time)->timestamp;
            $db->where('add_time', '<',new \MongoDB\BSON\UTCDateTime($e_time * 1000));
        }

        $db->orderby('add_time','desc');

        $data = $db->paginate((int)$page_num);
        $data->each(function ($item){
            $item->add_time = date('Y-m-d H:i:s',$item->add_time->__tostring()/1000);
            $item->bet_time = date('Y-m-d H:i:s',$item->bet_time->__tostring()/1000);
        });

        return $this->response()->array([
        'code'          => 0,
        'text'          => trans('delivery.success'),
        'result'        => $data
    ]);
    }

    /**
     * @api {get} /debugAccount 联调账号调用接口统计日志
     * @apiDescription 联调账号调用接口统计列表
     * @apiGroup log
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} agent 联调代理
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "data": [
    {
    "apiName": "获取SecurityKey",
    "agent": "csjj11",
    "status": "联调成功(7)",
    "succeds": 7,
    "sum": 11
    },
    {
    "apiName": "玩家登录游戏",
    "agent": "csjj11",
    "status": "未联调",
    "succeds": 0,
    "sum": 0
    },
    {
    "apiName": "获取供应商会员信息",
    "agent": "csjj11",
    "status": "未联调",
    "succeds": 0,
    "sum": 0
    },
    {
    "apiName": "会员充值",
    "agent": "csjj11",
    "status": "未联调",
    "succeds": 0,
    "sum": 0
    },
    {
    "apiName": "会员取款",
    "agent": "csjj11",
    "status": "未联调",
    "succeds": 0,
    "sum": 0
    },
    {
    "apiName": "会员存取款状态查询",
    "agent": "csjj11",
    "status": "未联调",
    "succeds": 0,
    "sum": 0
    },
    {
    "apiName": "时间段获取注单信息",
    "agent": "csjj11",
    "status": "未联调",
    "succeds": 0,
    "sum": 0
    },
    {
    "apiName": "获取注单信息",
    "agent": "csjj11",
    "status": "未联调",
    "succeds": 0,
    "sum": 0
    },
    {
    "apiName": "玩家离线通知",
    "agent": "csjj11",
    "status": "未联调",
    "succeds": 0,
    "sum": 0
    },
    {
    "apiName": "时间段获取异常注单信息",
    "agent": "csjj11",
    "status": "未联调",
    "succeds": 0,
    "sum": 0
    }
    ]
    }
    }
     */
    public function DebugAccount(Request $request)
    {
        $agent = $request->input('agent');
        $message = [
            'agent.required' => trans('agent.agent_name.required'),
            'agent.regex' => trans('agent.agent_name.regex'),
        ];
        $validator = \Validator::make($request->input(), [
            'agent' => [
                'required',
                'regex:/^[a-zA-z][a-zA-Z0-9_]{5,19}$/'
            ],
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        // 检测是否为联调账号
        $DB = new DB();
        $where = [
            'user_name' =>  $agent,
            'account_state' => 1,//正常账号
            'account_type' => 3,//联调账号
            'grade_id' => 2,//代理账号
        ];

        // 查询是否为联调账号
        $agent_ = Agent::where($where)->first();
        if(empty($agent_)){
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.debugAccount'),
                'result'=>'',
            ]);
        }

        $db = DebugAccount::select();;
        if(!empty($agent))
        {
            $db->where('agent','like','%'.$agent.'%');
        }

        $db->orderBy('sum','desc');
        $res['data'] = $db->get()->toArray();

        // 比对联调接口状态
        $this->checkDebugAccountStatus($res);

        return  $this->response()->array([
            'code'          => 0,
            'text'          => trans('delivery.success'),
            'result'        => $res
        ]);
    }


    /**
     * @api {get} /sys/chatInfoCount 联调账号注单数统计
     * @apiDescription 联调账号调用接口统计列表
     * @apiGroup log
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} agent 联调代理
     * @apiParam {Date} start_date 开始时间
     * @apiParam {Date} end_date 结束时间
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 1,//总页数
    "per_page": 10,//每页数
    "current_page": 1,//当前页
    "data": [
    {
    "count_num": 33,//注单数
    "agent_name": "p6api312"//联调代理登录名
    }
    ]
    }
    }
     */
    public function chatInfoCount(Request $request)
    {
        $agent = $request->input('agent');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $page = $request->input('page',1);
        $page_num = $request->input('page_num',10);
        $skip = (int) ($page-1) * $page_num;
        $limit = (int) $page_num;

        // 检测是否为联调账号
        if(!empty($agent)){
            $DB = new DB();
            $where = [
                'user_name' =>  $agent,
                'account_state' => 1,//正常账号
                'account_type' => 3,//联调账号
                'grade_id' => 2,//代理账号
            ];

            // 查询是否为联调账号
            $agent_ = Agent::where($where)->first();
            if(empty($agent_)){
                return $this->response->array([
                    'code'=>400,
                    'text'=>trans('agent.debugAccount'),
                    'result'=>'',
                ]);
            }

            $total_data = $data = self::agentCountOne($agent_['user_name'],$start_date, $end_date);
            if(empty($data)) {
                return  $this->response()->array([
                    'code'          => 400,
                    'text'          => trans('delivery.empty_list'),
                    'result'        => ''
                ]);
            }
        }else{
            // 查询出来联调厅主 注单总数
            $total_data = self::agentCountAll( $start_date, $end_date);
            $data = $count_data = array_slice($total_data,$skip,$limit);
            if(empty($data)) {
                return  $this->response()->array([
                    'code'          => 400,
                    'text'          => trans('delivery.empty_list'),
                    'result'        => ''
                ]);
            }
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => [
                'total' => count($total_data),
                'per_page' => $page_num,
                'current_page' => $page,
                'data' => $data,
            ],
        ]);
    }



    /**
     * 获取联调状态
     * @param $data
     */
    private  function checkDebugAccountStatus(&$data)
    {
        if(empty($data['data'])) return false;

        // 获取api全部接口名称
        $allApiName = config('apigame.apiName');

        // 获取已经联调的接口名称
        $debugApiName = array_column($data['data'],'apiName');

        // 获取为联调的接口
        $noDebugApiName = array_diff($allApiName,$debugApiName);

        // 自动填充字段
        $noDebugApiData = [];
        foreach ($noDebugApiName as $noDebugApi){
            $noDebugApiData[] = [
                'apiName'=> $noDebugApi,
                'agent'=> $data['data'][0]['agent'],
                'status'=> '未联调',
                'succeds'=> 0,
                'sum'=> 0
            ];
        }

        // 当成功一次则为联调成功
        foreach ($data['data'] as &$debugApiData){
            if($debugApiData['succeds']){
                $debugApiData['status'] = "联调成功";
            }else{
                $debugApiData['status'] = "联调失败";
            }
        }

        $data['data'] = array_merge($data['data'],$noDebugApiData);
    }

    /**
     * 获取单个联调代理的统计注单数
     * @param string $agent
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    private function  agentCountOne(string $agent ,$start_date = '' ,$end_date = ''):array
    {
        $findWhere['agent_name'] = $agent;
        $db = UserChartInfo::select();
        if(!empty($start_date)&& !empty($end_date) ){
            $start_date = new \MongoDB\BSON\UTCDateTime(strtotime($start_date)* 1000);
            $end_date = new \MongoDB\BSON\UTCDateTime(strtotime($end_date)* 1000);
            $db->where('start_time','>=',$start_date);
            $db->where('start_time','<=',$end_date);
        }
        $res['agent_name'] = $agent;
        $res['count_num'] =  $db->where($findWhere)->count();
        return  [$res];
    }

    /**
     *  获取全部联调代理的统计注单数
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    private function  agentCountAll($start_date = '' , $end_date = ''):array
    {
        // 查询出来所有的联调的厅主
        $agent_data = self::debugAcountInfo();
        $match['$match'] ['agent_id'] = [ '$in'=> $agent_data ];
        $project = ['$project'=> [ 'agent_name'=> 1,  'count_num'=>1]];
        $sort = ['$sort'=> ['count_num'=>1] ];
        $group = ['$group'=> [
            '_id' => ['agent_name'=>'$agent_name'],
            'count_num'=> ['$sum' => 1],
            'agent_name'=> ['$first' => '$agent_name'],

        ] ];

        $aggregate = [$match, $group, $sort, $project];
        if(!empty($start_date)&& !empty($end_date) ){
            $start_date = new \MongoDB\BSON\UTCDateTime(strtotime($start_date)* 1000);
            $end_date = new \MongoDB\BSON\UTCDateTime(strtotime($end_date)* 1000);
            $match['$match']['start_time'] = ['$gte'=> $start_date, '$lte'=>  $end_date];
            $aggregate = [$match,$group, $sort, $project];
        }
        
       $data =  UserChartInfo::raw(function($collection) use($aggregate) {
            return $collection->aggregate($aggregate);
        })->toArray();
       return $data;
    }

    /**
     * 获取全部联调代理的Id
     * @return array
     */
    private  function debugAcountInfo():array
    {
        $DB = new DB();
        $where = [
            'account_state' => 1,//正常账号
            'account_type' => 3,//联调账号
            'grade_id' => 2,//代理账号
        ];

        $agent_ = Agent::where($where)->get()->toArray();
        $data = array_column($agent_,'id');

        return $data;
    }

    /**
     * 获取全部联调厅主的Id
     * @return array
     */
    private  function debugHallInfo():array
    {
        $DB = new DB();
        $where = [
            'account_state' => 1,//正常账号
            'account_type' => 3,//联调账号
            'grade_id' => 1,//厅主账号
            'is_hall_sub' => 0,//厅主账号
        ];

        $hall_ = Agent::where($where)->get()->toArray();
        $data = array_column($hall_,'id');

        return $data;
    }


}