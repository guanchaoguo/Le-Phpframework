<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/5/31
 * Time: 10:07
 *  账户相关统计
 * 包含：厅主旗下代理统计、代理旗下玩家统计信息
 *
 */
namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\Admin\V1\BaseController;
use App\Models\Agent;
use App\Models\StatisOnlineUser;
use App\Models\StatisCash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;//引入时间扩展库

class AccountStatisticsController extends BaseController
{
    /**
     * @api {get} /index/hallActiveUser 首页厅主活跃会员排名数据统计
     * @apiDescription 首页厅主活跃会员排名数据统计
     * @apiGroup index
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} Success-Response:
     *  {
        "code": 0,
        "text": "操作成功",
        "result": {
            "data": [
            {
            "id": 1,  //id
            "user_name": "csj", //厅主登录名称
            "active_user_number": 100, //活跃会员数量

            }
            ]
        }
    }
     *
     */
    public function IndexActiveMemberByHall(Request $request)
    {
        $beginToday= date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y')));
        $endToday = date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1);
        $list = DB::table('statis_active_user')
            ->select(DB::raw('id,hall_name as user_name,sum(active_user) as active_user_number'))
            ->where('add_time','>=',$beginToday)->where('add_time','<=',$endToday)->orderBy('active_user_number','desc')->groupBy('hall_id')->take(10)->get()->toArray();
        //返回数据
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>['data'=>$list],
        ]);
    }


    /**
     * @api {get} /index/hallNumber 首页厅主总数
     * @apiDescription 首页厅主总数
     * @apiGroup index
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} Success-Response:
     *  {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total_hall_number": 200,
    }
    }
     *
     */
    public function IndexCountHallNumber(Request $request)
    {
        $countNumber = Agent::where('account_state','!=',3)->where(['grade_id'=>1,'is_hall_sub'=>0,'account_type'=>1])->count();
        $number = $countNumber ? $countNumber : 0;
        //返回数据
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>['total_hall_number'=>$number],
        ]);
    }

    /**
     * @api {get} /system/countHallAndNewHall  平台数据统计，厅主/代理总数和新增厅主/代理总数
     * @apiDescription 平台数据统计，厅主/代理总数和新增厅主/代理总数
     * @apiGroup system
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Int} type 数据类型，1为厅主类型数据，2为代理商数据，默认为1
     * @apiSuccessExample {json} Success-Response:
     * 厅主数据
     *  {
        "code": 0,
        "text": "操作成功",
        "result": {
            "xAxis": [1,2,3,4,5,6],
            "new": [51, 84, 84, 6, 84, 96, 52, 1, 49],
            "count": [63, 5, 4, 64, 89, 126, 98, 9, 864],
        }
    }
     *
     */
    public function SysHallAndNewHall(Request $request)
    {
        $type = $request->input('type',1);
        $table = DB::table('lb_agent_user');
        $xAxis = [];
        $countList = [];
        $newAddList = [];

        //生成近12个月横坐标操作
        $daterange = createMonth();
        foreach($daterange as $date){
            $xAxis[] = $date->format("Y-m");
        }

        if($type == 1)
        {
            //根据月份进行分组获取新增厅主数量
            $table->select(DB::raw('count(id) as add_user,DATE_FORMAT(add_time,\'%Y-%m\') as add_month'));
            $newAUserddList = $table->where(['grade_id'=>1,'is_hall_sub'=>0])->whereNotIn('account_type',[2,3])->groupBy('add_month')->get()->toArray();

            //获取到截止月份总的厅主数量
            foreach ($xAxis as $k=>$v)
            {
                $dateMonth = date('Y-m-d H:i:s',strtotime("$v +1 month -1 day"));
                $countList[] = DB::table('lb_agent_user')->where(['grade_id'=>1,'is_hall_sub'=>0])->where('add_time','<',"$dateMonth")->whereNotIn('account_type',[2,3])->count();
            }
        }
        else
        {
            //代理总数量和新增代理数量
            //根据月份进行分组获取新代理商主数量
            $table->select(DB::raw('count(id) as add_user,DATE_FORMAT(add_time,\'%Y-%m\') as add_month'));
            $newAUserddList = $table->where(['grade_id'=>2,'is_hall_sub'=>0])->whereNotIn('account_type',[2,3])->groupBy('add_month')->get()->toArray();

            //获取到截止月份总的厅主数量
            foreach ($xAxis as $k=>$v)
            {
                $dateMonth = date('Y-m-d H:i:s',strtotime("$v +1 month -1 day"));
                $countList[] =  DB::table('lb_agent_user')->where(['grade_id'=>2,'is_hall_sub'=>0])->whereNotIn('account_type',[2,3])->where('add_time','<',"$dateMonth")->count();
            }
        }
        foreach ($newAUserddList as $key=>$val)
        {
           // var_export($val);die;
            $xAxisDate = $val->add_month;
            $key = array_search($xAxisDate,$xAxis);
            //组装总注单数量
            $newAddList[$key] = $val->add_user;
        }
        foreach ($xAxis as $k1=>$v1)
        {
            if(!isset($newAddList[$k1]))
            {
                $newAddList[$k1] = 0;
            }
        }
        ksort($newAddList);
        $result = [
            'xAxis' => $xAxis,
            'new' =>$newAddList,
            'count' => $countList
        ];
        //返回数据
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$result,
        ]);
    }


    /**
     *  所有厅主/代理总数和新增厅主/代理数量
     */
    public function AllHallNumber()
    {

    }

    /**
     * @api {get} /hall/agentCount 厅主数据统计，单个厅主旗下代理商总数和新增代理商数量
     * @apiDescription 厅主数据统计，单个厅主旗下代理商总数和新增代理商数量
     * @apiGroup gain
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} user_name 厅主登录名称
     * @apiSuccessExample {json} Success-Response:
     *  {
        "code": 0,
        "text": "操作成功",
        "result": {
            "xAxis": [1,2,3,4,5,6],
            "new": [51, 84, 84, 6, 84, 96, 52, 1, 49],
            "count": [63, 5, 4, 64, 89, 126, 98, 9, 864],
        }
    }
     *
     */
    public function CountAgentByHall(Request $request)
    {
        $user_name = $request->input('user_name');
        $table = DB::table('lb_agent_user');
        $xAxis = [];
        $countList = [];
        $newAddList = [];

        //判断厅主名称是否存在
        if(!$user_name)
        {
            //返回数据
            return $this->response->array([
                'code'=>400,
                'text'=> trans('statistics.hall_name.required'),
                'result'=>'',
            ]);
        }

        //生成近12个月横坐标操作
        $daterange = createMonth();
        foreach($daterange as $date){
            $xAxis[] = $date->format("Y-m");
        }

        //获取搜索的厅主ID
        $hall = $table->where(['grade_id'=>1,'is_hall_sub'=>0,'user_name'=>$user_name])->first();
        if(!$hall)
        {
            //返回数据
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.hall_not_exist'),
                'result'=>'',
            ]);
        }
        //根据月份进行分组获取新增代理主数量
        $newAUserddList = DB::table('lb_agent_user')->select(DB::raw('count(id) as add_user,DATE_FORMAT(add_time,\'%Y-%m\') as add_month'))->where(['grade_id'=>2,'is_hall_sub'=>0,'parent_id'=>$hall->id])->groupBy('add_month')->get()->toArray();

        //获取到截止月份总的代理商数量
        foreach ($xAxis as $k=>$v)
        {
            $dateMonth = date('Y-m-d H:i:s',strtotime("$v +1 month -1 day"));
            $countList[] = DB::table('lb_agent_user')->where(['grade_id'=>2,'is_hall_sub'=>0,'parent_id'=>$hall->id])->where('add_time','<',"$dateMonth")->count();
        }

        foreach ($newAUserddList as $key=>$val)
        {
            $xAxisDate = $val->add_month;
            $key = array_search($xAxisDate,$xAxis);
            //组装总注单数量
            $newAddList[$key] = $val->add_user;
        }
        foreach ($xAxis as $k1=>$v1)
        {
            if(!isset($newAddList[$k1]))
            {
                $newAddList[$k1] = 0;
            }
        }
        ksort($newAddList);
        $result = [
            'xAxis' => $xAxis,
            'new' =>$newAddList,
            'count' => $countList
        ];
        //返回数据
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$result,
        ]);
    }

    /**
     * @api {get} /hall/AgentUser 代理商数据统计，单个代理旗下玩家总数和新增玩家数量
     * @apiDescription 代理商数据统计，单个代理旗下玩家总数和新增玩家数量
     * @apiGroup gain
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} user_name 厅主登录名称
     * @apiSuccessExample {json} Success-Response:
     *  {
        "code": 0,
        "text": "操作成功",
        "result": {
            "xAxis": [1,2,3,4,5,6],
            "users": [51, 84, 84, 6, 84, 96, 52, 1, 49],
            "addUsers": [63, 5, 4, 64, 89, 126, 98, 9, 864],
        }
    }
     *
     */
    public function CountUserByAgent(Request $request)
    {
        $user_name = $request->input('user_name');
        $xAxis = [];
        $countList = [];
        $newAddList = [];

        //判断代理商名称是否存在
        if(!$user_name)
        {
            //返回数据
            return $this->response->array([
                'code'=>400,
                'text'=> trans('statistics.agent_name.required'),
                'result'=>'',
            ]);
        }

        //生成近12个月横坐标操作
        $daterange = createMonth();
        foreach($daterange as $date){
            $xAxis[] = $date->format("Y-m");
        }

        $table = DB::table('lb_user');

        //根据月份进行分组获取新增玩家数量
        $table->select(DB::raw('count(uid) as add_user,DATE_FORMAT(create_time,\'%Y-%m\') as add_month'));
        $newAUserddList = $table->where(['user_rank'=>0,'agent_name'=>$user_name])->groupBy('add_month')->get()->toArray();

        //获取到截止月份总的玩家数量
        foreach ($xAxis as $k=>$v)
        {
            $dateMonth = date('Y-m-d H:i:s',strtotime("$v +1 month -1 day"));
            $countList[] = DB::table('lb_user')->where(['user_rank'=>0,'agent_name'=>$user_name])->where('create_time','<',"$dateMonth")->count();
        }
        foreach ($newAUserddList as $key=>$val)
        {
            $xAxisDate = $val->add_month;
            $key = array_search($xAxisDate,$xAxis);
            //组装总注单数量
            $newAddList[$key] = $val->add_user;
        }
        foreach ($xAxis as $k1=>$v1)
        {
            if(!isset($newAddList[$k1]))
            {
                $newAddList[$k1] = 0;
            }
        }
        ksort($newAddList);
        $result = [
            'xAxis' => $xAxis,
            'new' =>$newAddList,
            'count' => $countList
        ];
        //返回数据
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$result,
        ]);

    }

    /**
     * @api {get} /hall/activeUser 厅主/代理统计，所有厅主/代理旗下的会员数和活跃会员数据统计
     * @apiDescription 厅主/代理统计，所有厅主/代理旗下的会员数和活跃会员数据统计
     * @apiGroup gain
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Int} type 数据类型，1为厅主数据，2为代理商数据，默认为1
     * @apiParam {String} orderBy 排序的字段名称
     * @apiParam {String} user_name 厅主用户名/代理商用户名 （搜索条件）
     * @apiSuccessExample {json} Success-Response:
     *  {
        "code": 0,
        "text": "操作成功",
        "result": {
        "data": [
            {
            "id": 1, //ID
            "user_name": "csj", // 厅主登录
            "total_agent_number": 100,//代理总数
            "total_user_number": ,100 ,//会员总数
            "active_user_by_day": 100,//日活跃会员
            }
        ]
        }
    }
     *
     */
    public function AllActiveMemberByHall()
    {

    }

    /**
     * @api {get} /user/ActiveAndNewAddUser 玩家统计，所有新增的玩家和活跃玩家的数据统计(天、月)
     * @apiDescription 玩家统计，所有新增的玩家和活跃玩家的数据统计(天、月)
     * @apiGroup userGain
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Int} type 时间类型，1为天数据，2为月数据，默认为1
     * @apiSuccessExample {json} Success-Response:
     *  {
    "code": 0,
    "text": "操作成功",
    "result": {
    "xAxis": [1,2,3,4,5,6],
    "series":[
    {data: [2, 5, 6, 0, 0, 0], name: "新增玩家"},
    {data: [3, 5, 8, 9, 0, 0], name: "活跃玩家"}
    ]
    }
    }
     *
     */
    public function ActiveAndNewAddUser(Request $request)
    {
        $type =  $request->input('type') ?? 1;

        $ids = Agent::where([ 'grade_id' => 2, 'is_hall_sub' => 0])->whereIn('account_type',[2,3])->pluck('id');

        switch ($type) {
            //按天统计、近15天
            case 1:
                $date_format = [
                    (new Carbon('-14 Day'))->format('Y-m-d'),
                    (new Carbon('-13 Day'))->format('Y-m-d'),
                    (new Carbon('-12 Day'))->format('Y-m-d'),
                    (new Carbon('-11 Day'))->format('Y-m-d'),
                    (new Carbon('-10 Day'))->format('Y-m-d'),
                    (new Carbon('-9 Day'))->format('Y-m-d'),
                    (new Carbon('-8 Day'))->format('Y-m-d'),
                    (new Carbon('-7 Day'))->format('Y-m-d'),
                    (new Carbon('-6 Day'))->format('Y-m-d'),
                    (new Carbon('-5 Day'))->format('Y-m-d'),
                    (new Carbon('-4 Day'))->format('Y-m-d'),
                    (new Carbon('-3 Day'))->format('Y-m-d'),
                    (new Carbon('-2 Day'))->format('Y-m-d'),
                    (new Carbon('-1 Day'))->format('Y-m-d'),
                    (new Carbon('this Day'))->format('Y-m-d'),
                ];
                $addUser = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
                $activeUser = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];

                $start_date = (new Carbon('-14 Day'))->startOfDay()->toDateTimeString();
                $end_date = Carbon::now()->toDateTimeString();
                //新增玩家数
                $addUserData = \DB::table('lb_user')->select(array(
                    \DB::raw('DATE_FORMAT(create_time,"%Y-%m-%d") as days'),
                    \DB::raw('count(uid) as count')
                ))->where('create_time','>=',$start_date)->where('create_time','<',$end_date)->whereNotIn('agent_id', $ids)->groupby('days')->orderby('days')->get();

                if($addUserData) {
                    for ($i = 0; $i < 15; $i++) {
                        foreach ($addUserData as $v) {
                            if($date_format[$i] == $v->days) {
                                $addUser[$i] = round($v->count,2);
                                break;
                            }
                        }
                    }
                }

                //活跃玩家数
                $activeUserData = \DB::table('statis_active_user')->select(array(
                    \DB::raw('DATE_FORMAT(add_time,"%Y-%m-%d") as days'),
                    \DB::raw('SUM(active_user) as active_user'),
                ))->where('add_time','>=',$start_date)->where('add_time','<',$end_date)->whereNotIn('agent_id', $ids)->groupby('days')->orderby('add_time')->get();

                if($activeUserData) {
                    for ($i = 0; $i < 15; $i++) {
                        foreach ($activeUserData as $v) {
                            if($date_format[$i] == $v->days) {
                                $activeUser[$i] = round($v->active_user,2);
                                break;
                            }
                        }
                    }
                }

                break;
            //按月统计、近12个月
            case 2:
                $date_format = [
                    (new Carbon('-11 Month'))->format('Y-m'),
                    (new Carbon('-10 Month'))->format('Y-m'),
                    (new Carbon('-9 Month'))->format('Y-m'),
                    (new Carbon('-8 Month'))->format('Y-m'),
                    (new Carbon('-7 Month'))->format('Y-m'),
                    (new Carbon('-6 Month'))->format('Y-m'),
                    (new Carbon('-5 Month'))->format('Y-m'),
                    (new Carbon('-4 Month'))->format('Y-m'),
                    (new Carbon('-3 Month'))->format('Y-m'),
                    (new Carbon('-2 Month'))->format('Y-m'),
                    (new Carbon('-1 Month'))->format('Y-m'),
                    (new Carbon('this Month'))->format('Y-m'),
                ];
                $addUser = [0,0,0,0,0,0,0,0,0,0,0,0];
                $activeUser = [0,0,0,0,0,0,0,0,0,0,0,0];

                $start_date = (new Carbon('-11 Month'))->startOfDay()->toDateTimeString();

                $end_date = Carbon::now()->toDateTimeString();

                //新增玩家数
                $addUserData = \DB::table('lb_user')->select(array(
                    \DB::raw('DATE_FORMAT(create_time,"%Y-%m") as months'),
                    \DB::raw('count(uid) as count')
                ))->where('create_time','>=',$start_date)->where('create_time','<',$end_date)->whereNotIn('agent_id', $ids)->groupby('months')->orderby('months')->get();

                if($addUserData) {
                    for ($i = 0; $i < 12; $i++) {
                        foreach ($addUserData as $v) {
                            if($date_format[$i] == $v->months) {
                                $addUser[$i] = round($v->count,2);
                                break;
                            }
                        }
                    }
                }

                //活跃玩家数
                $activeUserData = \DB::table('statis_active_user')->select(array(
                    \DB::raw('DATE_FORMAT(add_time,"%Y-%m") as months'),
                    \DB::raw('SUM(active_user) as active_user'),
                ))->where('add_time','>=',$start_date)->where('add_time','<',$end_date)->whereNotIn('agent_id', $ids)->groupby('months')->orderby('add_time')->get();

                if($activeUserData) {
                    for ($i = 0; $i < 12; $i++) {
                        foreach ($activeUserData as $v) {
                            if($date_format[$i] == $v->months) {
                                $activeUser[$i] = round($v->active_user,2);
                                break;
                            }
                        }
                    }
                }
                break;
            default :
                return $this->response->array([
                    'code' => 400,
                    'text' => trans('agent.param_error'),
                    'result' => '',
                ]);
        }



        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => [
                'xAxis' => $date_format,
                'series' => [
                    [
                        'name' => 'addUser',
                        'data' => $addUser,
                    ],
                    [
                        'name' => 'activeUser',
                        'data' => $activeUser,
                    ],
                ],
            ],
        ]);
    }

    /**
     * @api {get} /online/user 玩家在线统计，昨日和今日在线玩家数据统计
     * @apiDescription 玩家在线统计，昨日和今日在线玩家数据统计
     * @apiGroup userGain
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} Success-Response:
     *  {
        "code": 0,
        "text": "操作成功",
        "result": {
            "series":[
                {data: [2, 5, 6, 0, 0, 0], name: "今日在线"},
                {data: [3, 5, 8, 9, 0, 0], name: "昨日在线"}
            ]
        }
    }
     *
     */
    public function CountUserOnline()
    {
        //今天的开始与当前时间
        $todayStartTime = Carbon::now()->startOfDay()->toDateTimeString();
        $todayEndTime = Carbon::now()->toDateTimeString();
        //昨天的开始于结束时间
        $yestodayStartTime = (new Carbon('-1 Day'))->startOfDay()->toDateTimeString();
        $yestodayEndTime = (new Carbon('-1 Day'))->endOfDay()->toDateTimeString();

        //统计今天在线
        $todayData = StatisOnlineUser::select('online_user','date_scale')->where('add_date', '>=', $todayStartTime)->where('add_date', '<=', $todayEndTime)->orderby('id')->get();
        //统计昨天在线
        $yestodayData = StatisOnlineUser::select('online_user','date_scale')->where('add_date', '>=', $yestodayStartTime)->where('add_date', '<=', $yestodayEndTime)->orderby('id')->get();

        //刻度数组
        $scales = config('scale');

        //今日在线处理start----
        $today_data = [];
        $today_last_scale = $todayData->last()['date_scale'];
        foreach ($scales as $scale) {
            if($scale > $today_last_scale) {
                break;
            }
            foreach ($todayData as $k => $v) {
                if($scale == $v['date_scale']) {
                    $today_data[$scale] = $v['online_user'];
                    unset($todayData[$k]);
                    break;
                } else {
                    $today_data[$scale] = 0;
                    break;
                }
            }
        }
        //今日在线处理end----

        //昨日在线处理start----
        $yestoday_data = [];
        foreach ($scales as $scale) {
            foreach ($yestodayData as $k => $v) {
                if($scale == $v['date_scale']) {
                    $yestoday_data[$scale] = $v['online_user'];
                    unset($yestodayData[$k]);
                    break;
                } else {
                    $yestoday_data[$scale] = 0;
                    break;
                }
            }
        }
        //昨日在线处理end----

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => [
                'series' => [
                    [
                        'name' => '今日在线',
                        'data' => array_values($today_data),
                    ],
                    [
                        'name' => '昨日在线',
                        'data' => array_values($yestoday_data),
                    ],
                ],
            ],
        ]);

    }


    /**
     *
     */
}