<?php
/**
 * Created by PhpStorm.
 * User: Sanji
 * Date: 2017/10/16
 * Time: 9:56
 */
namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use MongoDB\BSON\UTCDateTime;

class MonitorController  extends BaseController
{

    /**
     * @api {get} /monitor 监控管理列表
     * @apiDescription 监控管理列表
     * @apiGroup monitor
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @ apiSampleRequest http://app-loc.dev/api/monitor
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "data": [
    {
    "id": 1,
    "hall_id": 0,
    "name": "刷水",
    "tag": "M001",
    "paas": 0,
    "status": 1,
    "rule": []
    },
    {
    "id": 2,
    "hall_id": 0,
    "name": "大额投注",
    "tag": "M002",
    "paas": 0,
    "status": 1,
    "rule": {
    "bet": 20000,
    "gap": 5
    }
    },
    {
    "id": 3,
    "hall_id": 0,
    "name": "高盈利",
    "tag": "M003",
    "paas": 0,
    "status": 1,
    "rule": {
    "profit": 200,
    "gap": 5
    }
    },
    {
    "id": 4,
    "hall_id": 0,
    "name": "连胜次数",
    "tag": "M004",
    "paas": 0,
    "status": 1,
    "rule": {
    "win_streak": 10,
    "gap": 5
    }
    },
    {
    "id": 5,
    "hall_id": 0,
    "name": "胜率",
    "tag": "M005",
    "paas": 0,
    "status": 1,
    "rule": {
    "victory_ratio": 200,
    "gap": 5
    }
    }
    ]
    }
    }
     */
    public function list(Request $request)
    {
        $res = DB::table("sys_monitor")->where(['hall_id'=>0])->get()->toArray();
        //var_export($res);die;
        if($res)
        {
            foreach ($res as $k=>&$v)
            {
                //获取对应的规则
               $rule =  DB::table("sys_monitor_rule")->where(["tag"=>$v->tag,'hall_id'=>0])->get()->toArray();
                if(!$rule) {
                    $v->rule = [];
                    continue;
                }
                foreach ($rule as $k1=>$v1)
                {
                    $v->rule[$v1->keycode] = $v1->value;
                }
            }
        }
        return $this->response->array([
            'code' => 0,
            'text' =>trans('monitor23.success'),
            'result' => [
                'data' => $res,
            ],
        ]);
    }

    /**
     * @api {put} /monitor  设置单个监控项参数
     * @apiDescription 设置单个监控项参数
     * @apiGroup monitor
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} tag 修改设置的监控项标识符
     * @apiParam {String} tule 监控项的具体规则参数（数组格式,例如：['profit'=>200,'gap'=>5]）
     * @ apiSampleRequest http://app-loc.dev/api/monitor
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "保存成功",
    "result": ""
    }
     */
    public function setMonitor(Request $request)
    {
        $tag = $request->input('tag');
        $rule = $request->input('rule');
        if(!$tag || !is_array($rule))
        {
            return $this->response->array([
                'code' => 0,
                'text' =>trans('monitor23.invalid_error'),
                'result' => ''
            ]);
        }
        //校验具体规则值是否有填写
        $is_true = true;
        foreach ($rule as $k=>$v)
        {
            if (empty($v))
            {
                $is_true = false;
                break;
            }
        }
        if(!$is_true)
        {
            return $this->response->array([
                'code' => 0,
                'text' =>trans('monitor23.not_null'),
                'result' => ''
            ]);
        }

        //查看修改的规则是否存在
        $list = DB::table("sys_monitor_rule")->where(["tag"=>$tag,'hall_id'=>0])->get()->toArray();
        if(!$list)
        {
            return $this->response->array([
                'code' => 0,
                'text' =>trans('monitor23.not_exists'),
                'result' => ''
            ]);
        }

        //进行记录的修改（循环修改，不进行删除后添加，防止监控服务器出错）,事物处理
        DB::beginTransaction();
        foreach ($rule as $k=>$v)
        {
            $update = DB::table("sys_monitor_rule")->where(["tag"=>$tag,"keycode"=>$k,'hall_id'=>0])->update(["value"=>$v,'last_date'=>date("Y-m-d H:i:s",time())]);
            if(!$update)
            {
                DB::rollBack();
            }
        }

        //更新redis数据
        $redisStatus = $this->setRedis($tag);
        if(!$redisStatus)
        {
            DB::rollBack();

            //提示操作失败
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.fails'),
                'result' => ''
            ]);
        }
        DB::commit();



        return $this->response->array([
            'code' => 0,
            'text' =>trans('monitor23.success'),
            'result' =>""
        ]);

    }

    //监控规则或者监控状态修改时同步信息到redis
    private function setRedis($tag)
    {
        //更新redis数据
        $find = DB::table("sys_monitor")->where(["tag"=>$tag,'hall_id'=>0])->select('*')->first();
        $ruleList = DB::table("sys_monitor_rule")->where(["tag"=>$tag,'hall_id'=>0])->select('tag','keycode','value')->get()->toArray();

        foreach (StringShiftToInt($ruleList,["value"]) as $k=>$v)
        {
            $hashData[$v->keycode] = $v->value;
//            $hashData['tag'] = $v->tag;
        }
        $hashData['status'] = $find->status;
        $redis = Redis::connection("monitor");
        $res = $redis->hMset(env('MONITOR_RULE').":".$tag.":0",$hashData);
        if(!$res)
        {
            return false;
        }
        return true;
    }

    /**
     * @api {put} /monitor/status  设置单个监控项状态
     * @apiDescription 设置单个监控项参数
     * @apiGroup monitor
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} tag 修改设置的监控项标识符
     * @apiParam {Number} status  应用状态，0为关闭，1为开启，默认为0
     * @ apiSampleRequest http://app-loc.dev/api/monitor
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "保存成功",
    "result": ""
    }
     */
    public function setStatus(Request $request)
    {
        $tag = $request->input('tag');
        $status = $request->input('status',0);

        //判断记录是否存在
        $find = DB::table("sys_monitor")->where(['tag'=>$tag,'hall_id'=>0])->first();
        if(!$find)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.not_exists'),
                'result' =>""
            ]);
        }
        //判断状态字段是否合法
        if(!in_array($status,[0,1]))
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.invalid_error'),
                'result' => ''
            ]);
        }

        //进行正常修改操作
        DB::beginTransaction();
        $res = DB::table("sys_monitor")->where(['tag'=>$tag,'hall_id'=>0])->update(['status'=>$status]);

        //更新redis数据
        $redisStatus = $this->setRedis($tag);
        if(!$redisStatus || !$res)
        {
            DB::rollBack();

            //提示操作失败
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.fails'),
                'result' => ''
            ]);
        }
        DB::commit();


        return $this->response->array([
            'code' => 0,
            'text' =>trans('monitor23.success'),
            'result' => ''
        ]);
    }

    /**
     * @api {get} /trigger 获取监控数据列表
     * @apiDescription  获取监控数据列表(公用接口，所有的监控数据都用该接口，只是具体的tag参数值不同)
     * @apiGroup monitor
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} page 当前页 默认1
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiParam {String} tag 具体的监控项tag
     * @apiSuccessExample {json} Success-Response:
     * {
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
    "_id": {
    "$oid": "59e83a8685eb834c4fc7c506"
    },
    "user_id": 1,
    "user_name": "user001",
    "hall_name": "csj",
    "hall_id": 1,
    "agent_name": "agent001",
    "agent_id": 2,
    "rule_tag": "M003",         //触发的监控规则tag
    "user_real_value": "10000",     //用户真实的监控数据值
    "rule_value": "5000",       //具体监控项的阀值
    "number": 2,
    "last_trigger_date": "2017-10-19 02:24:49",
    "remark": "人工造的数据，后期可能会调整结构或者字段",
    "create_date": "2017-10-19 02:24:49",
    "ip_str": "192.168.31.155",
    "is_send_email": 1,
    "pass": 0,
    "begin_balance": 10000,     //用户初始余额（高盈利监控）
    "monitor_balance": 20000       //监控报警时用户余额（高盈利监控）
    }
    ]
    }
    }
     */
    public function  getLog(Request $request)
    {
        $tag = $request->input('tag');
        $is_page = (int) $request->input('is_page', 1);
        $page_num = (int) $request->input('page_num', env('PAGE_NUM', 10));
        if(!$tag)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.invalid_error'),
                'result' => ''
            ]);
        }
        
        //只获取当天的记录
        $start_date = new UTCDateTime(strtotime(date("Y-m-d",time())) * 1000);
        $end_date = new UTCDateTime(strtotime(date("Y-m-d",time())." 23:59:59") * 1000);
        $obj = DB::connection('mongodb')->table('trigger_log')->select('*');
        $obj->where(['rule_tag'=>$tag]);
        $obj->where(['pass'=>0]);
        $obj->where('create_date','>=',$start_date);
        $obj->where('create_date','<=',$end_date);
        $obj->orderBy('create_date','desc');

        if( $is_page ) {
            $data = $obj->paginate($page_num)->toArray();
        } else {
            $data = $obj->get()->toArray();
        }
        //进行时间格式的转换
        if($data['data'])
        {
            foreach ($data['data'] as $k=>&$v)
            {
                $v['create_date'] = date("Y-m-d H:i:s",$v['create_date']->__toString()/1000);
                $v['last_trigger_date'] = date("Y-m-d H:i:s",$v['last_trigger_date']->__toString()/1000);
            }
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('monitor23.success'),
            'result' =>  $is_page ? $data : ['data' => $data],
        ]);
    }

    /**
     * @api {get} /push/list 查看报警列表
     * @apiDescription  查看报警列表
     * @apiGroup monitor
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} page 当前页 默认1
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiSuccessExample {json} Success-Response:
     * {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 1,
    "per_page": 2,
    "current_page": 1,
    "last_page": 1,
    "next_page_url": null,
    "prev_page_url": null,
    "from": 1,
    "to": 1,
    "data": [
    {
    "_id": {
    "$oid": "59e5a2f085eb834c4fc7210c"
    },
    "rule_tag": "M001",
    "pass": 0,
    "user_name": "A001",
    "hall_id": 1,
    "hall_name": "HALL_001",
    "agent_id": 2,
    "agent_name": "agent_009",
    "remark": "手动造的数据",
    "create_date": "2017-10-15 02:24:49"
    }
    ]
    }
    }
     */
    public function getPushLog(Request $request)
    {
        $is_page = (int) $request->input('is_page', 1);
        $page_num = (int) $request->input('page_num', env('PAGE_NUM', 10));

        //只获取当天的记录
        $start_date = new UTCDateTime(strtotime(date("Y-m-d",time())) * 1000);
        $end_date = new UTCDateTime(strtotime(date("Y-m-d",time())." 23:59:59") * 1000);
        $obj = DB::connection('mongodb')->table('alarm_push_log')->select();
        $obj->where('create_date','>=',$start_date);
        $obj->where('create_date','<=',$end_date);
        $obj->where(['pass'=>0]);
        $obj->orderBy('create_date','desc');

        if( $is_page ) {
            $data = $obj->paginate($page_num)->toArray();
        } else {
            $data = $obj->get()->toArray();
        }
        foreach ($data['data'] as $k=>&$v)
        {
            $v['create_date'] = date("Y-m-d H:i:s",$v['create_date']->__toString()/1000);
        }
        return $this->response->array([
            'code' => 0,
            'text' => trans('monitor23.success'),
            'result' =>  $is_page ? $data : ['data' => $data],
        ]);
    }

    /**
     * @api {get} /alarm/list 报警账号列表
     * @apiDescription  报警账号列表
     * @apiGroup monitor
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} page 当前页 默认1
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiSuccessExample {json} Success-Response:
     * {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 4,
    "per_page": 2,
    "current_page": 1,
    "last_page": 2,
    "next_page_url": "http://platform.dev/api/alarm/list?page=2",
    "prev_page_url": null,
    "from": 1,
    "to": 2,
    "data": [
    {
    "id": 2,
    "hall_id": 0,
    "mobile": "13525566985",
    "email": "3026@qq.com",
    "last_date": "2017-10-17 02:15:34"
    },
    {
    "id": 3,
    "hall_id": 0,
    "mobile": "13525566985",
    "email": "30262@qq.com",
    "last_date": "2017-10-17 02:16:19"
    }
    ]
    }
    }
     */
    public function alarmList(Request $request)
    {
        $is_page = (int) $request->input('is_page', 1);
        $page_num = (int) $request->input('page_num', env('PAGE_NUM', 10));
        $obj = DB::table('sys_alarm_account')->where(['hall_id'=>0])->select('*');

        if( $is_page ) {
            $data = $obj->paginate($page_num);
        } else {
            $data = $obj->get();
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('monitor23.success'),
            'result' =>  $is_page ? $data : ['data' => $data],
        ]);
    }

    /**
     * @api {post} /alarm 添加报警账号
     * @apiDescription  添加报警账号
     * @apiGroup monitor
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token *
     * @apiParam {String} locale 语言 *
     * @apiParam {String} mobile 手机号码 *
     * @apiParam {String} email 邮箱 *
     * @apiSuccessExample {json} 成功返回：
     * {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function addAlarm(Request $request)
    {
        $id = $request->input('id');
        $data['mobile'] = $request->input('mobile');
        $data['email'] = $request->input('email');
        $data['hall_id'] = 0;
        $data['last_date'] = date('Y-m-d H:i:s',time());

        $message = [
            'mobile.required' => trans('monitor23.mobile.required'),
            'email.required' => trans('monitor23.email.required'),
            'email.email' => trans('monitor23.email.email'),
        ];
        $validator = \Validator::make($request->input(), [
            'mobile' => 'required',
            'email' => 'required|email',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        //进行正常添加操作
        $res = DB::table('sys_alarm_account')->insert($data);
        if(!$res)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.fails'),
                'result' => ''
            ]);
        }

        return $this->response->array([
            'code' => 0,
            'text' =>trans('monitor23.success'),
            'result' => ''
        ]);
    }

    /**
     * @api {get} /alarm 修改报警账号时获取信息
     * @apiDescription  修改报警账号时获取信息
     * @apiGroup monitor
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token *
     * @apiParam {String} locale 语言 *
     * @apiParam {Number} id 记录ID *
     * @apiSuccessExample {json} 成功返回：
     * {
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "data": {
    "id": 1,
    "hall_id": 0,
    "mobile": "13525566985",
    "email": "3026@qq.com",
    "type": 0,
    "last_date": "2017-10-17 02:01:14"
    }
    }
    }
     */
    public function getAlarmInfo(Request $request)
    {
        $id = $request->input('id');
        if(!$id)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.invalid_error'),
                'result' => ''
            ]);
        }
        //判断记录是否存在
        $find = DB::table('sys_alarm_account')->where(['id'=>$id,'hall_id'=>0])->first();
        if(!$find)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.not_exists'),
                'result' =>""
            ]);
        }

        //返回信息操作
        return $this->response->array([
            'code' => 0,
            'text' =>trans('monitor23.success'),
            'result' => ['data' => $find],
        ]);

    }

    /**
     * @api {put} /alarm 修改保存报警账号
     * @apiDescription  修改保存报警账号
     * @apiGroup monitor
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token *
     * @apiParam {String} locale 语言 *
     * @apiParam {Number} id 数据ID *
     * @apiParam {String} mobile 手机号码 *
     * @apiParam {String} email 邮箱 *
     * @apiSuccessExample {json} 成功返回：
     * {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function updateAlarm(Request $request)
    {
        $id = $request->input('id');
        $data['mobile'] = $request->input('mobile');
        $data['email'] = $request->input('email');
        $data['hall_id'] = 0;
        $data['last_date'] = date('Y-m-d H:i:s',time());

        $message = [
            'mobile.required' => trans('monitor23.mobile.required'),
            'email.required' => trans('monitor23.email.required'),
            'email.email' => trans('monitor23.email.email'),
        ];
        $validator = \Validator::make($request->input(), [
            'mobile' => 'required',
            'email' => 'required|email',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        //判断记录是否存在
        $find = DB::table('sys_alarm_account')->where(['id'=>$id,'hall_id'=>0])->first();
        if(!$find)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.not_exists'),
                'result' =>""
            ]);
        }

        //进行正常修改操作
        $res = DB::table('sys_alarm_account')->where(['id'=>$id,'hall_id'=>0])->update($data);
        if(!$res)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.fails'),
                'result' => ''
            ]);
        }

        return $this->response->array([
            'code' => 0,
            'text' =>trans('monitor23.success'),
            'result' => ''
        ]);

    }

    /**
     * @api {delete} /alarm 删除报警账号
     * @apiDescription  删除报警账号
     * @apiGroup monitor
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token *
     * @apiParam {String} locale 语言 *
     * @apiParam {Number} id 数据ID *
     * @apiSuccessExample {json} 成功返回：
     * {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function deleteAlarm(Request $request)
    {
        $id = $request->input('id');
        if(!$id)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.invalid_error'),
                'result' => ''
            ]);
        }

        //判断记录是否存在
        $find = DB::table('sys_alarm_account')->where(['id'=>$id,'hall_id'=>0])->first();
        if(!$find)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.not_exists'),
                'result' =>""
            ]);
        }

        //进行正常删除操作
        $de = DB::table('sys_alarm_account')->where(['id'=>$id,'hall_id'=>0])->delete();
        if(!$de)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.fails'),
                'result' => ''
            ]);
        }

        return $this->response->array([
            'code' => 0,
            'text' =>trans('monitor23.success'),
            'result' => ''
        ]);
    }

}