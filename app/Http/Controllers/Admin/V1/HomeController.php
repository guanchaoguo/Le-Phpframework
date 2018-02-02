<?php
namespace App\Http\Controllers\Admin\V1;

use Carbon\Carbon;
use App\Models\Player;
use App\Models\Agent;
use Illuminate\Http\Request;
/**
 * Class HomeController
 * @package App\Http\Controllers\Admin\V1
 * @desc 首页统计
 */
class HomeController extends BaseController
{
    public function __construct()
    {
    }

    /**
     * @api {get} /statistics/today/money 统计-今日派彩、总投注、赢钱最多的代理，输钱最多的代理
     * @apiDescription 统计-今日派彩、总投注、赢钱最多的代理，输钱最多的代理
     * @apiGroup home
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
            "result": {
            "total_bet_score": 500,
            "total_win_score": -500,
            "total_win_agent": {
            "agent_name": "",
            "money": 0
            },
            "total_lose_agent": {
            "agent_name": "",
            "money": 0
            }
            }
            }
     */
    public function getTodayMoney()
    {
        $data = [
            'total_bet_score' => 0,//今日总投注额
            'total_win_score' => 0,//今日派彩额
//            'total_win_agent' => [//今日赢钱最多的代理商
//                'agent_name' => '',//代理
//                'money' => 0,//金额
//            ],
//            'total_lose_agent' => [//今日输钱最多的代理商
//                'agent_name' => '',//代理
//                'money' => 0,//金额
//            ],
        ];

//        $dt = Carbon::today();
//        $year = $dt->year;
//        $month = $dt->month;
//        $day = $dt->day;

        $toDayTime = date("Y-m-d");
        $statis_cash = \DB::table('statis_cash')->where(["add_date"=>$toDayTime])->orderby('id','desc')->first();

        if( $statis_cash ) {
            $data['total_bet_score'] = number_format($statis_cash->total_bet_score, 2);
            $data['total_win_score'] = number_format($statis_cash->total_win_score, 2);
        }

//        //获取测试代理id
//        $ids = Agent::where([ 'grade_id' => 2, 'is_hall_sub' => '0'])->whereIn('account_type',[2,3])->pluck('id');
//
//        $where = [
//            'day_year' => $year,
//            'day_month' => $month,
//            'day_day' => $day,
//        ];
//
//        $cash_agent_win = \DB::table('statis_cash_agent')->where($where)->where('operator_win_score','>',0)->whereNotIn('agent_id', $ids)->orderby('operator_win_score', 'desc')->first();
//
//
//        //商家赢的钱
//        if( $cash_agent_win ) {
//            $data['total_win_agent'] = [
//                'agent_name' => $cash_agent_win->agent_name,//代理
//                'money' => number_format($cash_agent_win->operator_win_score,2),//金额
//            ];
//        }
//
//        $cash_agent_lose = \DB::table('statis_cash_agent')->where($where)->where('operator_win_score','<',0)->whereNotIn('agent_id', $ids)->orderby('operator_win_score','asc')->first();
//
//        if( $cash_agent_lose ) {
//            $data['total_lose_agent'] = [
//                'agent_name' => $cash_agent_lose->agent_name,//代理
//                'money' => number_format($cash_agent_lose->operator_win_score,2),//金额
//            ];
//        }

        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$data,
        ]);
    }

    /**
     * @api {get} /statistics/today/user 统计-当前在线玩家、今日用户渠道
     * @apiDescription 统计-当前在线玩家、今日用户渠道
     * @apiGroup home
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
        "result": {
        "online_user": 3,
        "user_channel": [
        {
        "name": "h5",
        "value": 0
        },
        {
        "name": "app",
        "value": 0
        },
        {
        "name": "pc",
        "value": 0
        }
        ]
        }
        }
     */
    public function getTodayUser()
    {
        $dt = date('Y-m-d',time());

        $login_device = \DB::table('statis_login_device')->where('add_date',$dt)->first();

        //获取测试厅主id
        $ids = Agent::where([ 'grade_id' => 1, 'is_hall_sub' => 0])->whereIn('account_type',[2,3])->pluck('id');
        //不统计测试账号
        $online_user = Player::where('on_line','Y')->whereNotIn('hall_id',$ids)->count('uid');
        $data = [
            'online_user' => $online_user,
            'user_channel' => [
                [
                    'name' =>'h5',
                    'value' => $login_device ? $login_device->device_h5 : 0,
                ],
                [
                    'name' =>'app',
                    'value' =>$login_device ? $login_device->device_app : 0,
                ],
                [
                    'name' =>'pc',
                    'value' =>$login_device ? $login_device->device_pc : 0,
                ],
            ],
        ];

        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$data,
        ]);
    }


    public function getWeekData()
    {

        //上周的开始时间
        $last_week_start_date = (new Carbon('-2 Sunday'))->toDateTimeString();
        //上周的结束时间
        $last_week_end_date = (new Carbon('last Sunday'))->toDateTimeString();
        //本周的开始时间
        $this_week_start_date = $last_week_end_date;
        //本周的结束时间
        $this_week_end_date = (new Carbon('this Sunday'))->toDateTimeString();

        //上周添加代理数
        $last_week_add_agent = self::countAgents($last_week_start_date,$last_week_end_date,2);
        //本周添加代理数
        $this_week_add_agent = self::countAgents($this_week_start_date,$this_week_end_date,2);

        //获取测试代理id
        $ids = Agent::where(['grade_id' => 2, 'is_hall_sub' => '0'])->whereIn('account_type',[2,3])->pluck('id');

        //上周总投注额
        $last_week_bet_score = \DB::table('statis_cash_agent')->where('add_date','>=',$last_week_start_date)->where('add_date','<',$last_week_end_date)->whereNotIn('agent_id', $ids)->sum('total_bet_score');

        //上周总派彩额
        $last_week_win_score = \DB::table('statis_cash_agent')->where('add_date','>=',$last_week_start_date)->where('add_date','<',$last_week_end_date)->whereNotIn('agent_id', $ids)->sum('total_win_score');

        //本周总投注额
        $this_week_bet_score = \DB::table('statis_cash_agent')->where('add_date','>=',$this_week_start_date)->where('add_date','<',$this_week_end_date)->whereNotIn('agent_id', $ids)->sum('total_bet_score');

        //本周总派彩额
        $this_week_win_score = \DB::table('statis_cash_agent')->where('add_date','>=',$this_week_start_date)->where('add_date','<',$this_week_end_date)->whereNotIn('agent_id', $ids)->sum('total_win_score');

        $data = [
            'add_agent_num' => [
                'last_week' => (int)$last_week_add_agent,
                'this_week' => (int)$this_week_add_agent,
            ],
            'total_bet_score' => [
                'last_week' => number_format($last_week_bet_score,2),
                'this_week' => number_format($this_week_bet_score,2),
            ],
            'total_win_score' => [
                'last_week' => number_format($last_week_win_score,2),
                'this_week' => number_format($this_week_win_score,2),
            ],
        ];
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);
    }


    public function getMonthData()
    {

        //上月的开始、结束时间
        $last_month_start_date = (new Carbon('last Month'))->startOfMonth()->toDateTimeString();
        $last_month_end_date = (new Carbon('last Month'))->endOfMonth()->toDateTimeString();
        //本月的开始、结束时间
        $this_month_start_date = (new Carbon('this Month'))->startOfMonth()->toDateTimeString();
        $this_month_end_date = (new Carbon('this Month'))->endOfMonth()->toDateTimeString();

        //上月新增厅主
        $last_month_add_hall = self::countAgents($last_month_start_date,$last_month_end_date,1);
        //本月新增厅主
        $this_month_add_hall = self::countAgents($this_month_start_date,$this_month_end_date,1);
        //上月新增代理
        $last_month_add_agent = self::countAgents($last_month_start_date,$last_month_end_date,2);
        //本月新增代理
        $this_month_add_agent = self::countAgents($this_month_start_date,$this_month_end_date,2);

        //获取测试代理id
        $ids = Agent::where(['grade_id' => 2, 'is_hall_sub' => '0'])->whereIn('account_type',[2,3])->pluck('id');

        //上月总投注额
        $last_month_bet_score = \DB::table('statis_cash_agent')->where('add_date','>=',$last_month_start_date)->where('add_date','<',$last_month_end_date)->whereNotIn('agent_id', $ids)->sum('total_bet_score');

        //上月总派彩额
        $last_month_win_score = \DB::table('statis_cash_agent')->where('add_date','>=',$last_month_start_date)->where('add_date','<',$last_month_end_date)->whereNotIn('agent_id', $ids)->sum('total_win_score');

        //本月总投注额
        $this_month_bet_score = \DB::table('statis_cash_agent')->where('add_date','>=',$this_month_start_date)->where('add_date','<',$this_month_end_date)->whereNotIn('agent_id', $ids)->sum('total_bet_score');

        //本月总派彩额
        $this_month_win_score = \DB::table('statis_cash_agent')->where('add_date','>=',$this_month_start_date)->where('add_date','<',$this_month_end_date)->whereNotIn('agent_id', $ids)->sum('total_win_score');

        $data = [
            'add_hall_num' => [
                'last_month' => (int)$last_month_add_hall,
                'this_month' => (int)$this_month_add_hall,
            ],
            'add_agent_num' => [
                'last_month' => (int)$last_month_add_agent,
                'this_month' => (int)$this_month_add_agent,
            ],
            'total_bet_score' => [
                'last_month' => number_format($last_month_bet_score,2),
                'this_month' => number_format($this_month_bet_score,2),
            ],
            'total_win_score' => [
                'last_month' => number_format($last_month_win_score,2),
                'this_month' => number_format($this_month_win_score,2),
            ],
        ];

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);
    }

    /**
     * @api {get} /statistics/user 统计-周（月）统计（用户）
     * @apiDescription 统计-周（月）统计（用户）
     * @apiGroup home
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} type 类型：1按周，2：按月
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     * {
        "code": 0,
        "text": "操作成功",
        "result": [
        {
        "name": "last_week/last_month",
        "type": "bar",
        "data": [
        23,//新增厅主
        21//新增代理
        ]
        },
        {
        "name": "this_week/this_month",
        "type": "bar",
        "data": [
        3,//新增厅主
        6//新增代理
        ]
        }
        ]
        }
     */
    public function getDataByUser(Request $request)
    {
        $type = (int)$request->input('type');

        switch ($type){
            //按周统计
            case 1:
                //上周的开始时间
                $last_start_date = (new Carbon('-2 Sunday'))->toDateTimeString();
                //上周的结束时间
                $last_end_date = (new Carbon('last Sunday'))->toDateTimeString();
                //本周的开始时间
                $this_start_date = $last_end_date;
                //本周的结束时间
                $this_end_date = (new Carbon('this Sunday'))->toDateTimeString();

                $last_name = 'last_week';
                $this_name = 'this_week';
                break;
            //按月统计
            case 2:

                //上月的开始、结束时间
                $last_start_date = (new Carbon('last Month'))->startOfMonth()->toDateTimeString();
                $last_end_date = (new Carbon('last Month'))->endOfMonth()->toDateTimeString();
                //本月的开始、结束时间
                $this_start_date = (new Carbon('this Month'))->startOfMonth()->toDateTimeString();
                $this_end_date = (new Carbon('this Month'))->endOfMonth()->toDateTimeString();
                $last_name = 'last_month';
                $this_name = 'this_month';
                break;
            default:
                return $this->response->array([
                    'code'=> 400,
                    'text'=> trans('agent.param_error'),
                    'result'=>'',
                ]);
                break;
        }

        //上新增厅主
        $last_add_hall = self::countAgents($last_start_date,$last_end_date,1);
        //本新增厅主
        $this_add_hall = self::countAgents($this_start_date,$this_end_date,1);
        //上新增代理
        $last_add_agent = self::countAgents($last_start_date,$last_end_date,2);
        //本新增代理
        $this_add_agent = self::countAgents($this_start_date,$this_end_date,2);

        $data = [
            [
                'name' => $last_name,
                'type' => 'bar',
                'data' =>[$last_add_hall,$last_add_agent],
            ],
            [
                'name' => $this_name,
                'type' => 'bar',
                'data' =>[$this_add_hall,$this_add_agent],
            ],
        ];
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);
    }

    /**
     * @api {get} /statistics/score 统计-周（月）统计（金额）
     * @apiDescription 统计-周（月）统计（金额）
     * @apiGroup home
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} type 类型：1按周，2：按月
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     * {
    "code": 0,
    "text": "操作成功",
    "result": [
    {
    "name": "last_week/last_month",
    "type": "bar",
    "data": [
    23,//总投注额
    21//总派彩
    ]
    },
    {
    "name": "this_week/this_month",
    "type": "bar",
    "data": [
    3,//总投注额
    6//总派彩
    ]
    }
    ]
    }
     */
    public function getDataByScore(Request $request)
    {
        $type = (int)$request->input('type');

        switch ($type){
            //按周统计
            case 1:
                //上周的开始时间
                $last_start_date = (new Carbon('-2 Sunday'))->toDateTimeString();
                //上周的结束时间
                $last_end_date = (new Carbon('last Sunday'))->toDateTimeString();
                //本周的开始时间
                $this_start_date = $last_end_date;
                //本周的结束时间
                $this_end_date = (new Carbon('this Sunday'))->toDateTimeString();

                $last_name = 'last_week';
                $this_name = 'this_week';
                break;
            //按月统计
            case 2:

                //上月的开始、结束时间
                $last_start_date = (new Carbon('last Month'))->startOfMonth()->toDateTimeString();
                $last_end_date = (new Carbon('last Month'))->endOfMonth()->toDateTimeString();
                //本月的开始、结束时间
                $this_start_date = (new Carbon('this Month'))->startOfMonth()->toDateTimeString();
                $this_end_date = (new Carbon('this Month'))->endOfMonth()->toDateTimeString();
                $last_name = 'last_month';
                $this_name = 'this_month';
                break;
            default:
                return $this->response->array([
                    'code'=> 400,
                    'text'=> trans('agent.param_error'),
                    'result'=>'',
                ]);
                break;
        }

        //获取测试代理id
        $ids = Agent::where([ 'grade_id' => 2, 'is_hall_sub' => '0'])->whereIn('account_type',[2,3])->pluck('id');

        //上周、月总投注额
        $last_bet_score = \DB::table('statis_cash_agent')->where('add_date','>=',$last_start_date)->where('add_date','<',$last_end_date)->sum('total_bet_score');

        //上周、月总派彩额
        $last_win_score = \DB::table('statis_cash_agent')->where('add_date','>=',$last_start_date)->where('add_date','<',$last_end_date)->whereNotIn('agent_id', $ids)->sum('total_win_score');

        //本周、月总投注额
        $this_bet_score = \DB::table('statis_cash_agent')->where('add_date','>=',$this_start_date)->where('add_date','<',$this_end_date)->whereNotIn('agent_id', $ids)->sum('total_bet_score');

        //本周、月总派彩额
        $this_win_score = \DB::table('statis_cash_agent')->where('add_date','>=',$this_start_date)->where('add_date','<',$this_end_date)->whereNotIn('agent_id', $ids)->sum('total_win_score');

        $data = [
            [
                'name' => $last_name,
                'type' => 'bar',
                'data' =>[round($last_bet_score,2),round($last_win_score,2)],
            ],
            [
                'name' => $this_name,
                'type' => 'bar',
                'data' =>[round($this_bet_score,2),round($this_win_score,2)],
            ],
        ];
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);

    }
    /**
     * @api {get} /statistics/semi-annual 统计-近半年的总投注额、总派彩额
     * @apiDescription 统计-近半年的总投注额、总派彩额
     * @apiGroup home
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     * {
        "code": 0,
        "text": "操作成功",
        "result": {
        "data": [
        "12月",
        "1月",
        "2月"
        ],
        "series": [
        {
        "name": "T1002",
        "type": "line",
        "data": [
        "23.0000",
        "20.0000",
        "20.0000"
        ]
        },
        {
        "name": "T1001",
        "type": "line",
        "data": [
        "232.0000",
        "352.0000",
        "110.0000"
        ]
        }
        ]
        }
        }
     */
    public function getSemiAnnualData()
    {
        //近半年的开始、结束时间
        $month_start_date = (new Carbon('-5 Month'))->startOfMonth()->toDateTimeString();

//        $month_end_date = (new Carbon('last Month'))->endOfMonth()->toDateTimeString();
        $month_end_date = Carbon::now()->toDateTimeString();

        //获取测试代理id
        $ids = Agent::where([ 'grade_id' => 2, 'is_hall_sub' => '0'])->whereIn('account_type',[2,3])->pluck('id');

        //近半年总投注额、总派彩额
        $score = \DB::table('statis_cash_agent')->select(array(
            \DB::raw('day_month as month'),
            \DB::raw('DATE_FORMAT(add_date,"%Y-%m") as add_date'),
            \DB::raw('SUM(total_bet_score) as bet_score'),
            \DB::raw('SUM(total_win_score) as win_score')
        ))->where('add_date','>=',$month_start_date)->where('add_date','<',$month_end_date)->whereNotIn('agent_id', $ids)->groupby('day_month')->orderby('day_year')->orderby('day_month')->get();
        //近6个月月份
        $month = [
            (new Carbon('-5 Month'))->format('Y-m'),
            (new Carbon('-4 Month'))->format('Y-m'),
            (new Carbon('-3 Month'))->format('Y-m'),
            (new Carbon('-2 Month'))->format('Y-m'),
            (new Carbon('-1 Month'))->format('Y-m'),
            (new Carbon('this Month'))->format('Y-m'),
        ];
        //总投注初始化
        $bet_score = [0,0,0,0,0,0];
        //总派彩初始化
        $win_score = [0,0,0,0,0,0];
        if($score) {
            for ($i = 0; $i < 6; $i++) {
                foreach ($score as $v) {
                    if($month[$i] == $v->add_date) {
                        $bet_score[$i] = round($v->bet_score,2);
                        $win_score[$i] = round($v->win_score,2);
                        break;
                    }
                }
            }
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => [
                'data' => $month,
                'series' => [
                    [
                        'name' => 'T1002',
                        'type' => 'line',
                        'data' => $win_score,
                    ],
                    [
                        'name' => 'T1001',
                        'type' => 'line',
                        'data' => $bet_score,
                    ],
                ],
            ],
        ]);
    }

    private static function countAgents($start_date, $end_date, $grade_id=0)
    {
        return Agent::where('add_time','>=',$start_date)->where('add_time','<',$end_date)->where('grade_id', $grade_id)->where('is_hall_sub',0)->whereNotIn('account_type',[2,3])->count();
    }
}