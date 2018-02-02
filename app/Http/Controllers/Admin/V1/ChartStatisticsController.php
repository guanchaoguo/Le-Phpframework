<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/5/31
 * Time: 10:26
 * 注单数据统计相关
 */
namespace App\Http\Controllers\Admin\V1;

use App\Models\StatisCash;
use App\Models\StatisBetDistribution;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;//引入时间扩展库
class ChartStatisticsController extends BaseController
{

    /**
     * @api {get} /index/chartNumber 首页统计今日总的注单数量
     * @apiDescription 首页统计今日总的注单数量
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
        "total_chart_number": 200, //总的注单数量
        }
    }
     *
     */
    public function ToDayChartNumber()
    {
        //当前的天数
        $dateToDay = date("Y-m-d");
        $find = StatisCash::where(['add_date'=>$dateToDay])->first();
        if($find) {
            $total_bet_count = $find->total_bet_count;
        } else {
            $total_bet_count = 0;
        }
        //返回数据
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>['total_chart_number'=>$total_bet_count],
        ]);
    }

    /**
     * @api {get} /system/sysChart  平台数据统计，天/月注单数据
     * @apiDescription 平台数据统计，天/月注单数据
     * @apiGroup system
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
                {data: [2, 5, 6, 0, 0, 0], name: "注单数"},
            ]
            }
    }
     *
     */
    public function sysChart(Request $request)
    {
        //月数据，统计12个月的所有厅主注单数量数据
        //天数据，统计最近15天所有厅主注单数量数据
        $type = $request->input('type',1);
        $table = DB::table('statis_cash_agent');
        if($type == 1)
        {   //按月进行分组，获取12个月数据
            $table->select(DB::raw('sum(total_bet_count) as total_bet_count,add_date'));
            $oldYear = date('Y-m-d',strtotime('-11 month'));
            $table->where('add_date','>=',$oldYear);
            $table->groupBy('day_month');
        }else
        {  //按天进行分组，获取最近15天数据
            $table->select(DB::raw('sum(total_bet_count) as total_bet_count,add_date'));
            $oldDay = date('Y-m-d',strtotime('-14 days'));
            $table->where('add_date','>=',$oldDay);
            $table->groupBy('add_date');
        }
        $list = $table->get()->toArray();
        $xAxis = [];//横坐标数组

        if($type == 1)
        {
            //生成近12个月横坐标操作
            $daterange = createMonth();
            foreach($daterange as $date){
                $xAxis[] = $date->format("Y-m");
            }
        }else
        {
            //生成近15天横坐标操作
            for($i=strtotime('-14 days');$i<=time();$i+=(3600*24))
            {
                $xAxis[] = date('Y-m-d',$i);
            }
        }
        if($list)
        {
            $total_bet_count =['name'=>'注单数','data'=>[]];
            foreach ($list as $k=>$v)
            {
                $xAxisDate = $type == 1 ? substr($v->add_date,0,7) : $v->add_date;
                $key = array_search($xAxisDate,$xAxis);
                //组装总注单数量
                $total_bet_count['data'][$key] = $list[$k]->total_bet_count;
                foreach ($xAxis as $k1=>$v1)
                {
                    if(!isset($total_bet_count['data'][$k1]))
                    {
                        $total_bet_count['data'][$k1] = 0;
                    }
                }
            }
        }
        ksort($total_bet_count['data']);
        $series = [$total_bet_count];
        $result = [
            'xAxis' => $xAxis,
            'series' =>$series
        ];
        //返回数据
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$result,
        ]);
    }

    /**
     * @api {get} /hall/charNumber 厅主数据统计，单个厅主天/月注单数量
     * @apiDescription 厅主数据统计，单个厅主天/月注单数量
     * @apiGroup gain
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} user_name 厅主登录名称
     * @apiParam {Int} type 时间类型，1为月数据，2为天数据，默认为1
     * @apiParam {Int} hall_type 数据类型，1为厅主数据，2为代理商数据，默认为1
     * @apiSuccessExample {json} Success-Response:
     *  {
    "code": 0,
    "text": "操作成功",
    "result": {
        "xAxis": [1,2,3,4,5,6],
        "series":[
            {data: [2, 5, 6, 0, 0, 0],"name": ""注单数},
        ]
    }
    }
     *
     */
    public function hallCharNumber(Request $request)
    {
        $type = $request->input('type',1);
        $hall_type = $request->input('hall_type',1);
        $user_name = $request->input('user_name');
        $xAxis = [];
        $total_bet_count = ['name'=>'注单数'];
        //判断厅主名称是否存在
        if(!$user_name)
        {
            //返回数据
            return $this->response->array([
                'code'=>400,
                'text'=> $hall_type == 1 ? trans('statistics.hall_name.required') : trans('statistics.agent_name.required'),
                'result'=>'',
            ]);
        }


        $table = DB::table('statis_cash_agent');
        if($type == 1)
        {//月数据
            //生成近12个月横坐标操作
            $daterange = createMonth();
            foreach($daterange as $date){
                $xAxis[] = $date->format("Y-m");
            }

            //按月进行分组，获取12个月数据
            $table->select(DB::raw('sum(total_bet_count) as total_bet_count,add_date'));
            $oldYear = date('Y-m-d',strtotime('-11 month'));
            $table->where('add_date','>=',$oldYear);

            if($hall_type == 1 && !empty($user_name))
            {//厅主数据
                $table->where(['hall_name'=>$user_name]);
            }else if($hall_type == 2 && !empty($user_name))
            {//代理商数据
                $table->where(['agent_name'=>$user_name]);
            }
            $table->groupBy('day_month');
        }else
        {//天数据
            //生成近15天横坐标操作
            for($i=strtotime('-14 days');$i<=time();$i+=(3600*24))
            {
                $xAxis[] = date('Y-m-d',$i);
            }
            //按天进行分组，获取最近15天数据
            $table->select(DB::raw('sum(total_bet_count) as total_bet_count,add_date'));
            $oldDay = date('Y-m-d',strtotime('-14 days'));
            $table->where('add_date','>=',$oldDay);

            if($hall_type == 1 && !empty($user_name))
            {//厅主数据
                $table->where(['hall_name'=>$user_name]);
            }else if($hall_type == 2 && !empty($user_name))
            {//代理商数据
                $table->where(['agent_name'=>$user_name]);
            }
            $table->groupBy('add_date');
        }
        $list = $table->get()->toArray();

        //进行数据组装
        foreach ($list as $k=>$v)
        {
            $xAxisDate = $type == 1 ? substr($v->add_date,0,7) : $v->add_date;
            $key = array_search($xAxisDate,$xAxis);
            //组装总注单数量
            $total_bet_count['data'][$key] = $list[$k]->total_bet_count;
        }

        foreach ($xAxis as $k1=>$v1)
        {
            if(!isset($total_bet_count['data'][$k1]))
            {
                $total_bet_count['data'][$k1] = 0;
            }
        }

        ksort($total_bet_count['data']);
        $series = [$total_bet_count];
        $result = [
            'xAxis' => $xAxis,
            'series' =>$series
        ];
        //返回数据
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$result,
        ]);
    }


    /**
     * @api {get} /online/charAmount 在线统计，当天每个小时时间段投注分布数据统计
     * @apiDescription 在线统计，当天每个小时时间段投注分布数据统计
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
        "result": [
            {
                name: '1-1000',
                data: [320, 302, 301, 334, 390, 330, 320,11,22]//数组长度24；
            },
             {
                name: '10001-10000',
                data: [320, 302, 301, 334, 390, 330, 320,11,22]//数组长度24；
            }]
    }
     *
     */
    public function ChartSectionByDate()
    {


        //初始化数据
        $data1 = $data2 = $data3 = $data4 = $data5 = $data6 = [
            0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
        ];

        //今天开始时间
        $todayStartTime = (new Carbon('this Day'))->startOfDay()->toDateTimeString();
        $data = StatisBetDistribution::select(array(
            '*',
            \DB::raw('DATE_FORMAT(bettime,"%H") as hour'),
        ))->where('bettime', '>=', $todayStartTime)->groupby('hour')->orderby('hour', 'asc')->get();


        foreach ($data as $k => $v) {
            $v['hour'] = (int)$v['hour'];
            $data1[$v['hour']] = $v['total1'];
            $data2[$v['hour']] = $v['total2'];
            $data3[$v['hour']] = $v['total3'];
            $data4[$v['hour']] = $v['total4'];
            $data5[$v['hour']] = $v['total5'];
            $data6[$v['hour']] = $v['total6'];

        }

        $re_data = [
            [
                'name' => '1-1000',
                'data' => $data1,
            ],
            [
                'name' => '1000-5000',
                'data' => $data2,
            ],
            [
                'name' => '5000-10000',
                'data' => $data3,
            ],
            [
                'name' => '10000-50000',
                'data' => $data4,
            ],
            [
                'name' => '50000-200000',
                'data' => $data5,
            ],
            [
                'name' => '200000-',
                'data' => $data6,
            ],
        ];
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $re_data,
        ]);
    }

    /**
     * @api {get} /online/chartNumber 在线统计，昨天和今天的投注区间注单数量统计
     * @apiDescription 在线统计，昨天和今天的投注区间注单数量统计
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
                {data: [2, 5, 6, 0, 0, 0], name: "今日"},
                {data: [3, 5, 8, 9, 0, 0], name: "昨日"}
            ]
        }
    }
     *
     */
    public function CountChartNumberByDays()
    {

        //今天的开始与当前时间
//        $todayStartTime = Carbon::now()->startOfDay()->toDateTimeString();
        $todayEndTime = Carbon::now()->toDateTimeString();

        //昨天的开始于结束时间
        $yestodayStartTime = (new Carbon('-1 Day'))->startOfDay()->toDateTimeString();
//        $yestodayEndTime = (new Carbon('-1 Day'))->endOfDay()->toDateTimeString();

        //统计今天注单数
        $data = StatisBetDistribution::select(array(
            \DB::raw('SUM(rank1) as rank1'),
            \DB::raw('SUM(rank2) as rank2'),
            \DB::raw('SUM(rank3) as rank3'),
            \DB::raw('SUM(rank4) as rank4'),
            \DB::raw('SUM(rank5) as rank5'),
            \DB::raw('SUM(rank6) as rank6'),
            \DB::raw('DATE_FORMAT(bettime,"%d") as day'),
        ))->where('bettime', '>=', $yestodayStartTime)->where('bettime', '<=', $todayEndTime)->orderby('bettime','desc')->groupby('day')->get()->toArray();


        $res_data = [
            [
                'data' => [0, 0, 0, 0, 0, 0],
                'name' => '今日注单数',
            ],
            [
                'data' => [0, 0, 0, 0, 0, 0],
                'name' => '昨日注单数',
            ],
        ];
        foreach ($data as $k => $v) {
            if( $v['day'] == date('d')) {
                unset($v['day']);
                $v = array_values($v);
                $res_data[0]['data'] = $v;
            } else {
                unset($v['day']);
                $v = array_values($v);
                $res_data[1]['data'] = $v;
            }

        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => [
                'series' => $res_data
            ],
        ]);
    }
}