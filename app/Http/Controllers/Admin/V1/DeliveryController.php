<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/4/5
 * Time: 10:57
 * 厅主交收控制器
 */
namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\Admin\V1\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\Agent;

class DeliveryController extends BaseController
{
    /**
     * @api {get} /issue 查看交收期数列表
     * @apiDescription 查看交收期数列表
     * @apiGroup issue
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiParam {Date} year 年份
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 10,
    "per_page": 10,
    "current_page": 1,
    "next_page_url": null,
    "prev_page_url": null,
    "from": 1,
    "to": 8,
    "data": [
    {
    "id": 3,            //ID
    "issue": "201703",  //期数名称
    "start_date": "2017-04-03 00:00:00",    //开始时间
    "end_date": "2017-04-04 00:00:00",      //结束时间
    "state": 1,
    "add_user": "",
    "add_time": "2017-04-05 14:43:51",  //添加时间
    "update_time": "0000-00-00 00:00:00" //修改时间
    },
    ]
    }
    }
     */
    public function index(Request $request)
    {
        $year = $request->input('year');
        $page_num = $request->input('page_num',12);
        $is_page = $request->input('is_page', 1);

        $db = DB::table('game_platform_delivery');
        if(!empty($year))
        {
            $db->where('issue','like',$year.'%');
        }
        $db->orderby('start_date','asc');
        if(!$is_page) {
            $list = $db->get()->toArray();
            $res['data'] = $list;
        } else {
            $res = $db->paginate($page_num)->toArray();
        }
        if(!$res['data'])
        {
            return  $this->response()->array([
                'code'          => 0,
                'text'          => trans('delivery.empty_list'),
                'result'        => ['data'=>[]]
            ]);
        }

        return  $this->response()->array([
            'code'          => 0,
            'text'          => trans('delivery.success'),
            'result'        => $res
        ]);

    }

    /**
     * @api {post} /issue 添加交收期数
     * @apiDescription 添加交收期数数据
     * @apiGroup issue
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} date 时间年月（例如：2017-01）
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function store(Request $request)
    {
        //验证错误信息自定义，提升验证信息友好度
        $message = [
            'date.required'     => trans('delivery.issue.required'),
        ];
        //进行提交数据验证操作
        $validate = \Validator::make($request->input(),[
            'date'     => 'required|date',
        ],$message);
        //数据格式验证不通过
        if($validate->fails())
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => $validate->errors(),
                'result'        => ''
            ]);
        }

        //生成期数标题，并验证期数的唯一性
        $issue = date("Ym",strtotime($request->input('date')));
        $find = DB::table('game_platform_delivery')
            ->where(['issue'=>$issue])
            ->first();
        if($find)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('delivery.issue_exist'),
                'result'    => ''
            ]);
        }

        //计算开始时间和结束时间
        $year = (int)substr( $request->input('date'),0,4);
        $month = (int)substr( $request->input('date'),5,2);


        $first_monday_number =  date("w", mktime(0, 0, 0, $month, 01, $year)) != 1 ? (date("w", mktime(0, 0, 0, $month, 01, $year)) == 0 ? 2 : 7 - date("w", mktime(0, 0, 0, $month, 01, $year)) + 2) : 1;
        $startDate = date("Y-m-d H:i:s", strtotime($year . "-" . $month . "-" . $first_monday_number));//拿到月份的第一个星期一时间


        //拿到下个月的第一个星期天
        if($month == 12)
        {
            $year = (int)$year +1;
            $month = 1;
            $first_monday_number =  date("w", mktime(0, 0, 0, $month, 01, $year)) != 1 ? (date("w", mktime(0, 0, 0, $month, 01, $year)) == 0 ? 2 : 7 - date("w", mktime(0, 0, 0, $month, 01, $year)) + 2) : date("w", mktime(0, 0, 0, $month, 01, $year));
            $endDate = date("Y-m-d H:i:s", strtotime($year . "-" . $month . "-" . $first_monday_number)-1);//拿到下年1月份的第一个星期一时间

        }else {
            $first_monday_number =  date("w", mktime(0, 0, 0, $month+1, 01, $year)) != 1 ? (date("w", mktime(0, 0, 0, $month+1, 01, $year)) == 0 ? 2 : 7 - date("w", mktime(0, 0, 0, $month+1, 01, $year)) + 2) : 1;
            $endDate = date("Y-m-d H:i:s", strtotime($year . "-" . ($month+1) . "-" . $first_monday_number)-1);//拿到下个月份的第一个星期一时间

        }


        $localTime = $request->input('localTime');
        //进行数据添加操作
        $res = DB::table('game_platform_delivery')->insert([
            'issue'=>$issue,
            'start_date'    => $startDate,
            'end_date'      => $endDate,
            'add_user'  => Auth::user()->user_name,
            'add_time'  => date('Y-m-d H:i:s',time()),
            'update_time'   => date('Y-m-d H:i:s',time()),
            'local_start_date'  => $startDate,
            'local_end_date'    => $endDate,
        ]);
        //error
        if(!$res)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('delivery.fails'),
                'result'    => ''
            ]);
        }
        @addLog(['action_name'=>'添加交收期数','action_desc'=>' 添加了一个交收期数，新添加的期数为：'.$issue,'action_passivity'=>'期数']);
        //success
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('delivery.success'),
            'result'    => ''
        ]);
    }

    //添加时校验开始时间和结束时间
    public function validateIssueDate($request)
    {
        $start_date = strtotime($request['start_date']);
        $end_date = strtotime($request['end_date']);

        //校验结束时间不能小于等于开始时间
        if($start_date >= $end_date)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('delivery.end_date.le_start'),
                'result'    => ''
            ]);
        }

        //获取数据库中开始时间最大的数据
        $startMax = DB::table('game_platform_delivery')->orderBy('start_date','desc')->first();
        //验证结束时间和数据库中的时间是否有交叉重叠
//        if($startMax && (strtotime($startMax->start_date) >= $end_date || strtotime($startMax->end_date) >= $end_date  || strtotime($startMax->start_date) >= $start_date))
        if($startMax && (strtotime($startMax->start_date) >= $start_date || strtotime($startMax->end_date) >= $start_date))
        {
            return $this->response->array([
                'code'      => 400,
                'text'      => trans('delivery.end_date.has_been'),
                'result'    => ''
            ]);
        }
        return false;

    }

    //编辑期数时校验开始时间和结束时间
    public function validateIssueDateUpdate($request,$id)
    {
        $start_date = strtotime($request['start_date']);
        $end_date = strtotime($request['end_date']);

        //校验结束时间不能小于等于开始时间
        if($start_date >= $end_date)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('delivery.end_date.le_start'),
                'result'    => ''
            ]);
        }

        //获取当前修改的上一条记录
        $startMax = DB::table('game_platform_delivery')->where('id','<',$id)->orderBy('start_date','desc')->first();
        //验证结束时间和数据库中的时间是否有交叉重叠
        if($startMax && (strtotime($startMax->end_date) >= $start_date))
        {
            return $this->response->array([
                'code'      => 400,
                'text'      => trans('delivery.end_date.has_been'),
                'result'    => ''
            ]);
        }

        //获取当前修改的下一条记录
        $nextMax = DB::table('game_platform_delivery')->where('id','>',$id)->orderBy('start_date','asc')->first();
        //验证结束时间和数据库中的时间是否有交叉重叠
        if($end_date && (strtotime($nextMax->start_date) <= $end_date))
        {
            return $this->response->array([
                'code'      => 400,
                'text'      => trans('delivery.end_date.has_been'),
                'result'    => ''
            ]);
        }
        return false;

    }

    /**
     * @api {patch} /issue/{id} 修改交收期数
     * @apiDescription 修改交收期数数据,{id}变量为期数id
     * @apiGroup issue
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} issue 期数标题
     * @apiParam {String} start_date 开始时间
     * @apiParam {String} end_date 结束时间
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function update(Request $request,$id)
    {
        //验证修改数据是否存在
        $find = DB::table('game_platform_delivery')->where(['id'=>$id])->first();
        if(!$find)
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('delivery.issue_not_exist'),
                'result'        => ''
            ]);
        }

        //验证错误信息自定义，提升验证信息友好度
        $message = [
            'issue.required'     => trans('delivery.issue.required'),
            'issue.numeric'     => trans('delivery.issue.numeric'),
            'start_date.required'         => trans('delivery.start_date.required'),
            'end_date.required'          => trans('delivery.end_date.required'),
        ];
        //进行提交数据验证操作
        $validate = \Validator::make($request->input(),[
            'issue'     => 'required|numeric',
            'start_date'     => 'required|date',
            'end_date'      => 'required|date'
        ],$message);
        //数据格式验证不通过
        if($validate->fails())
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => $validate->errors(),
                'result'        => ''
            ]);
        }

        //验证期数的唯一性
        $find = DB::table('game_platform_delivery')
            ->where(['issue'=>$request->input(['issue'])])
            ->where('id','<>',$id)
            ->first();
        if($find)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('delivery.issue_exist'),
                'result'    => ''
            ]);
        }

        //校验开始时间和结束时间
        $dateTime['start_date'] = $request->input('start_date');
        $dateTime['end_date'] = $request->input('end_date');
        $validateDate = $this->validateIssueDateUpdate($dateTime,$id);
        if( $validateDate !== false)
        {
            return $validateDate;
        }
        $localTime = $request->input('localTime');
        //进行数据修改操作
        $res = DB::table('game_platform_delivery')->where(['id'=>$id])->update([
            'issue'=>$request->input('issue'),
            'start_date'    => date("Y-m-d H:i:s",strtotime($request->input('start_date'))),
            'end_date'      => date("Y-m-d H:i:s",strtotime($request->input('end_date'))),
            'add_user'  => Auth::user()->user_name,
            'update_time'  => date('Y-m-d H:i:s',time()),
            'local_start_date'  => $localTime[0],
            'local_end_date'    => $localTime[1],
        ]);
        //error
        if(!$res)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('delivery.fails'),
                'result'    => ''
            ]);
        }

        @addLog(['action_name'=>'交收期数编辑','action_desc'=>' 对交收期数 '.$request->input('issue').'进行了编辑：','action_passivity'=>'期数']);
        //success
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('delivery.success'),
            'result'    => ''
        ]);
    }

    /**
     * @api {get} /delivery 查看交收数据列表
     * @apiDescription 查看交收数据列表
     * @apiGroup issue
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} issue 期数（不能为空）
     * @apiParam {Int} hall_id 厅主ID
     * @apiParam {Int} is_filter 是否执行过滤，0为不执行，1为执行，默认为1
     * @apiParam {String} user_name 厅主登录名
     * @apiParam {String} real_name 厅主用户名
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 10,
    "per_page": 10,
    "current_page": 1,
    "next_page_url": null,
    "prev_page_url": null,
    "from": 1,
    "to": 1,
    "data": [
    {
    "id": 1, //id
    "issue": "201701", //期数
    "p_name": "agent_test", //厅主名称
    "real_name": "陈教主", //厅主用户名
    "p_id": 2, //厅主ID
    "real_income": "240000.00"  //应交收金额
    "platform_profit": "100000.00", //期数对应厅主毛利润
    "scale": "10.00%", //平台占成比例
    "receipt": "10000.00", //游戏平台应收费用
    "roundot": "1000.00", //包网费用
    "line_map": "2000.00", //线路图
    "upkeep": "2000.00", //维护费
    "ladle_bottom": "3000.00", //包底费用
    "is_over": 1 //是否已经交收标记，0为否，1为真
    }
    ],
    "total_receipt": 18000 //本期应收总额
    "total_real": 18000 //本期实收总额
    }
    }
     */
    public function issueList(Request $request)
    {
        $issue = $request->input('issue');
        $hall_id = (int)$request->input('hall_id');
        $user_name = $request->input('user_name');
        $real_name = $request->input('real_name');
        $page_num = $request->input('page_num',10);
        $is_page = $request->input('is_page', 1);
        $is_filter = $request->input('is_filter',1);//为1时是进行过滤,0时不过滤
        $db = DB::table('game_platform_delivery_info');

        //获取测试厅主id
        $ids = Agent::where(['grade_id' => 1, 'is_hall_sub' => '0'])->whereIn('account_type',[2,3])->pluck('id')->toArray();

        //厅主属性控制是否显示交收数据
        $hallIds = [];
        if($is_filter)
        {
            //获取需要过滤的厅主ID
            $hallIds = Agent::where(['grade_id' => 1, 'is_hall_sub' => '0','show_delivery'=>0])->pluck('id')->toArray();
        }

        $match = array_merge($ids,$hallIds);


        if(!empty($issue))
        {
            $db->where('issue','=',$issue);
        }
        if(!empty($hall_id))
        {
            $db->where('p_id','=',$hall_id);
        }
        if(!empty($user_name))
        {
            $db->where('p_name','=',$user_name);
        }
        if(!empty($real_name))
        {
            $db->where('real_name','=',$real_name);
        }
        if(!empty($match))
        {
            $db->whereNotIn("p_id",$match);
        }


        $db->orderby('id', 'desc');
        if(!$is_page)
        {
            $list = $db->get()->toArray();
            $res['data'] = $list;
        }
        else
        {
            $res = $db->paginate($page_num)->toArray();
        }

        if(!$res['data'])
        {
            $res['total_real'] = 0;
            $res['total_receipt'] = 0;
            return  $this->response()->array([
                'code'          => 0,
                'text'          => trans('delivery.empty_list'),
                'result'        => $res
            ]);
        }


        //统计当前条件总的交收数据
        $res['total_receipt'] = 0.00;
        $res['total_real'] = 0.00;
        foreach ($res['data'] as $key=>$val)
        {
            //计算每个厅主每一期的应收款项
            $res['data'][$key]->real_income = number_format(($val->receipt > $val->ladle_bottom ? $val->receipt : $val->ladle_bottom) + $val->roundot + $val->line_map + $val->upkeep - $val->red_packets,2);
            //统计总的应收款
            $res['total_receipt'] += ($val->receipt > $val->ladle_bottom ? $val->receipt : $val->ladle_bottom) + $val->roundot + $val->line_map + $val->upkeep - $val->red_packets;
            $res['data'][$key]->scale = $val->scale;
            //统计实收金额
            if($val->is_over == 1)
            {
                $res['total_real'] += ($val->receipt > $val->ladle_bottom ? $val->receipt : $val->ladle_bottom) + $val->roundot + $val->line_map + $val->upkeep - $val->red_packets;
            }
            $val->is_over = (bool)$val->is_over;
        }
        $res['total_real'] = number_format($res['total_real'],2);
        $res['total_receipt'] = number_format($res['total_receipt'],2);
        return  $this->response()->array([
            'code'          => 0,
            'text'          => trans('delivery.success'),
            'result'        => $res
        ]);
    }

    /**
     * @api {patch} /delivery/{id} 标记已收操作
     * @apiDescription 标记已收操作
     * @apiGroup issue
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Int} state 状态 0为否，1为已收
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function editIssueState(Request $request,$id)
    {
        //验证数据是否存在
        $find = DB::table('game_platform_delivery_info')->where(['id'=>$id])->first();
        if(!$find)
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('delivery.issue_not_exist'),
                'result'        => ''
            ]);
        }

        //进行提交数据验证操作
        $validate = \Validator::make($request->input(),[
            'state'     => 'required|in:0,1',
        ]);
        //数据格式验证不通过
        if($validate->fails())
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => $validate->errors(),
                'result'        => ''
            ]);
        }

        //进行交收确认操作
        $state = DB::table('game_platform_delivery_info')->where(['id'=>$id])->update(['is_over'=>$request->input('state')]);
        if(!$state)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('delivery.fails'),
                'result'    => ''
            ]);
        }
        if($request->input('state') == 1)
        {
            @addLog(['action_name'=>'标记交收状态','action_desc'=>' 给厅主'.$find->p_name.'的'.$find->issue.'期数标记为已收','action_passivity'=>$find->p_name]);
        }else{
            @addLog(['action_name'=>'标记交收状态','action_desc'=>' 给厅主'.$find->p_name.'的'.$find->issue.'期数标记为变更为未收','action_passivity'=>$find->p_name]);
        }

        //success
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('delivery.success'),
            'result'    => ''
        ]);
    }

    /**
     * @api {get} /issue/{id} 编辑交收期数时获取数据
     * @apiDescription 编辑交收期数时获取数据
     * @apiGroup issue
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "id": 3,
    "issue": "201711",
    "start_date": "2017-04-08 00:00:00",
    "end_date": "2017-04-09 00:00:00",
    "state": 1,
    "add_user": "",
    "add_time": "2017-04-05 15:32:33",
    "update_time": "2017-04-05 15:32:53"
    }
    }
     */
    public function getIssue(Request $request,$id)
    {
        //获取数据
        $find = DB::table('game_platform_delivery')->where(['id'=>$id])->first();
        if(!$find)
        {
            return $this->response->array([
                'code'  => 400,
                'text'  => trans('delivery.issue_not_exist'),
                'result'    => ''
            ]);
        }

        //success
        return $this->response->array([
            'code'  => 0,
            'text'  => trans('delivery.success'),
            'result'    => $find
        ]);

    }

    //数据放到redis中
    public function setIssueRedis()
    {

        $res = DB::table('game_platform_delivery')->where('state','<>',2)->orderBy('end_date')->get()->toArray();
        if(!$res)
        {
            return false;
        }

        //写入到redis中
//        $key = env('ISSUE_DATA_FIX');
//        Redis::set($key,json_encode($res));
    }

    //从redis中获取数据
    public static function getIssueData()
    {
        $res = DB::table('game_platform_delivery')->where('state','<>',2)->orderBy('end_date')->get()->toArray();
        $res = json_decode(json_encode($res),true);
        if(!$res)
        {
            return false;
        }

        return $res;
    }

    //定时任务
    public function crontab()
    {
        DeliveryCrontabController::monitor();
    }


    /**
     * @api {post} /autoIssue 一键生成交收期数
     * @apiDescription 一键生成交收期数数据
     * @apiGroup issue
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} year 年份
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function autoCreateIssue(Request $request)
    {
        /**
         * 期数生成规则：本月第一个星期一到下个月的第一个星期天为一个期数结算周期
         */
        $year = $request->input('year');//所属年份
        $issueNumber = 12;//默认生成12期

        //验证错误信息自定义，提升验证信息友好度
        $message = [
            'year.required'         => trans('delivery203.year.required'),
        ];
        //进行提交数据验证操作
        $validate = \Validator::make($request->input(),[
            'year'     => 'required',
        ],$message);
        //数据格式验证不通过
        if($validate->fails())
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => $validate->errors()->first(),
                'result'        => ''
            ]);
        }

        //查看数据库中是否用参数年份的期数数据，如果有则跳过已有期数
        $oldList = DB::table('game_platform_delivery')->where('issue','like',$year.'%')->get()->toArray();
        $oldIssueList = array_column($oldList,'issue');




        //拿到参数年的第一个月的第一个星期一和下个月的第一个星期天

//        $endDate = date("Y-m-d",strtotime('this Sunday',strtotime('first day of next month',strtotime($startDate))));


        //根据所需要生成的期数数组进行循环生成期数数据(最大生成12期)
        $createData = [];
        $startDateList = [];
        for ($i = 1; $i <= $issueNumber +1 ; $i++) {

            if($i <= 12)
            {
                $startDate = $year . "-" . $i . "-01";//从每年的一月一号开始计算
                $first_monday = date("w", mktime(0, 0, 0, $i, 01, $year)) != 1 ? (date("w", mktime(0, 0, 0, $i, 01, $year)) == 0 ? 2 : 7 - date("w", mktime(0, 0, 0, $i, 01, $year)) + 2) : date("w", mktime(0, 0, 0, $i, 01, $year));
                $startDateList[] = date("Y-m-d H:i:s", strtotime($year . "-" . $i . "-" . $first_monday));//拿到月份的第一个星期一时间
            }else{

                $year = (int)$year +1;
                $month = 1;
                $first_monday = date("w", mktime(0, 0, 0, $month, 01, $year)) != 1 ? (date("w", mktime(0, 0, 0, $month, 01, $year)) == 0 ? 2 : 7 - date("w", mktime(0, 0, 0, $month, 01, $year)) + 2) : date("w", mktime(0, 0, 0, $month, 01, $year));
                $startDateList[] = date("Y-m-d H:i:s", strtotime($year . "-" . $month . "-" . $first_monday));//拿到下一年的第一个月份的第一个星期一时间
            }

        }

        foreach ($startDateList as $k=>$v) {

            $issue = date('Ym', strtotime($v));//期数名称
            if (in_array($issue, $oldIssueList)) {//数据库中已有期数，则不重新生成，跳出循环
                continue;
            }
            if ($k < 12) {
                $start_date = $v;
                $end_date = date("Y-m-d H:i:s", strtotime($startDateList[$k + 1]) - 1);

                //期数数据组装
                $createData[] = [
                    'issue' => $issue,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'state' => 1,
                    'add_user' => Auth::user()->user_name,
                    'add_time' => date('Y-m-d H:i:s', time()),
                    'update_time' => date('Y-m-d H:i:s', time()),
                    'local_start_date' => $start_date,
                    'local_end_date' => $end_date,
                ];
            }
        }

        //进行批量数据写入
        if($createData)
        {
            $res = DB::table('game_platform_delivery')->insert($createData);
        }else
        {
            $res = true;
        }


        //error
        if(!$res)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('delivery.fails'),
                'result'    => ''
            ]);
        }
        @addLog(['action_name'=>'一键生成交收期数','action_desc'=>" 一键生成交收期数，本次共生成 $issueNumber 期，所属年份为：".$year,'action_passivity'=>'期数']);
        //success
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('delivery.success'),
            'result'    => ''
        ]);
    }


}