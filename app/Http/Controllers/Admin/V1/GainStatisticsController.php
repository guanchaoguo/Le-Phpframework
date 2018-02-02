<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/5/31
 * Time: 9:36
 *  盈利数据统计控制器
 * 包含：厅主和代理的盈利数据
 */
namespace App\Http\Controllers\Admin\V1;

use App\Models\StatisCashAgent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GainStatisticsController extends \App\Http\Controllers\Admin\V1\BaseController
{

    /**
     * @api {get} /index/hallRanking 首页统计厅主排名
     * @apiDescription 首页统计厅主排名
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
                "operator_win_score": "100.00", //赢钱金额
            }
            ]
        }
        }
     *
     */
    public function IndexHallRanking(Request $request)
    {
        $list = StatisCashAgent::select(DB::raw('sum(operator_win_score) as operator_win_score,hall_id as id,hall_name as user_name'))
            ->where('add_date','=',date("Y-m-d"))->orderBy('operator_win_score','desc')->groupBy('hall_id')->limit(10)->get()->toArray();
        if($list)
        {
            foreach ($list as $key=>&$val)
            {
                $val['operator_win_score'] = sprintf("%.2f",$val['operator_win_score']);
            }
        }

        //返回数据
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>['data'=>$list],
        ]);
    }

    /**
     * @api {get} /system/sysGainData 平台数据统计天/月盈利
     * @apiDescription 平台数据统计天/月盈利
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
                {data: [2, 5, 6, 0, 0, 0], name: "派彩总额"},
               {data: [3, 5, 8, 9, 0, 0], name: "投注总额"}
          ]
        }
    }
     *
     */
    public function sysGainData(Request $request)
    {
        //月数据，统计12个月的所有厅主的投注总额数据和派彩数据
        //天数据，统计最近15天所有厅主的投注总额数据和派彩数据
        $type = $request->input('type',1);
        $table = DB::table('statis_cash_agent');
        if($type == 1)
        {   //按月进行分组，获取12个月数据
            $table->select(DB::raw('sum(operator_win_score) as operator_win_score, sum(total_bet_score) as total_bet_score,add_date'));
            $oldYear = date('Y-m-d',strtotime('-11 month'));
            $table->where('add_date','>=',$oldYear);
            $table->groupBy('day_month');
        }else
        {  //按天进行分组，获取最近15天数据
            $table->select(DB::raw('sum(operator_win_score) as operator_win_score, sum(total_bet_score) as  total_bet_score,add_date'));
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
            $operator_win_score =['name'=>'派彩总额','data'=>[]];
            $total_bet_score = ['name'=>'投注总额','data'=>[]];
            foreach ($list as $k=>$v)
            {
                $xAxisDate = $type == 1 ? substr($v->add_date,0,7) : $v->add_date;
                $key = array_search($xAxisDate,$xAxis);
                //组装派彩总额
                $operator_win_score['data'][$key] = sprintf("%.2f",$v->operator_win_score);
                //组装投注总额度
                $total_bet_score['data'][$key] = sprintf("%.2f",$v->total_bet_score);
                foreach ($xAxis as $k1=>$v1)
                {
                    if(!isset($total_bet_score['data'][$k1]))
                    {
                        $total_bet_score['data'][$k1] = sprintf("%.2f",0.00);
                    }
                    if(!isset($operator_win_score['data'][$k1]))
                    {
                        $operator_win_score['data'][$k1] = sprintf("%.2f",0.00);
                    }
                }
            }

        }
        ksort($operator_win_score['data']);
        ksort($total_bet_score['data']);
        $series = [$operator_win_score,$total_bet_score];
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
     *  所有厅主/代理，每天/月盈利数据数据统计
     *
     */
    public function HallGain()
    {

    }


    /**
     * @api {get} /hall/gain 厅主/代理统计，所有厅主/代理盈利数据数据统计
     * @apiDescription 厅主/代理统计，所有厅主/代理盈利数据数据统计
     * @apiGroup gain
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Int} type 数据类型，1为厅主数据，2为代理商数据，默认为1
     * @apiParam {String} orderBy 排序的字段名称
     * @apiParam {String} sort 排序的方式（desc 倒叙， asc 正序）
     * @apiParam {Array} field 需要显示的字段数组
     * @apiParam {Int} take 显示的数据条数，默认10条
     * @apiParam {String} user_name 厅主用户名/代理商用户名 （搜索条件）
     * @apiSuccessExample {json} Success-Response:
     *  {
        "code": 0,
        "text": "操作成功",
        "result": {
        "data": [
        {
            "id": 1, //ID
            "user_name": "csj",//厅主名称
            "operator_win_score": "100.00",//总派彩
            "month_win_score": "100.00",//月派彩
            "today_win_score": "100.00",//当天派彩
            "total_bet_score": "100.00",//投注总额
            "month_bet_score": "100.00",//月投注总额
            "day_bet_score": "100.00",//天投注总额
            "total_chart_number": 100,//总注单数
            "month_chart_number": 100,//月总注单数
            "day_chart_number": 100,//天总注单数
        }
        ]
        }
        }
     *
     */
    public function AllHallGain()
    {

    }

    /**
     * @api {get} /hall/singleHallGain 厅主数据统计，单个厅主/代理，每天/月的盈利数据
     * @apiDescription 厅主数据统计，单个厅主/代理，每天/月的盈利数据
     * @apiGroup gain
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} user_name 代理商/厅主登录名
     * @apiParam {Int} type 时间类型，1为月数据，2为天数据，默认为1
     * @apiParam {Int} hall_type 数据类型，1为厅主数据，2为代理商数据，默认为1
     * @apiSuccessExample {json} Success-Response:
     *  {
        "code": 0,
        "text": "操作成功",
        "result": {
        "xAxis": [1,2,3,4,5,6],
        "series":[
            {data: [2, 5, 6, 0, 0, 0],"name": ""派彩总额},
            {data: [3, 5, 8, 9, 0, 0],"name": "投注总额"}
        ]
        }
        }
     *
     */
    public function SingleHallGain(Request $request)
    {
        $type = $request->input('type',1);
        $hall_type = $request->input('hall_type',1);
        $user_name = $request->input('user_name');
        $xAxis = [];
        $operator_win_score = ['name'=>'派彩总额'];
        $total_bet_score = ['name'=> '投注总额'];
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
            $table->select(DB::raw('sum(operator_win_score) as operator_win_score, sum(total_bet_score) as total_bet_score,DATE_FORMAT(add_date,\'%Y-%m\') as add_month,day_month'));
            $oldYear = date('Y-m-d',strtotime('-11 month'));
            $table->where('add_date','>=',$oldYear);

            //判断是获取厅主数据还是代理商数据
            if($hall_type == 1 && !empty($user_name))
            {//厅主数据
                $table->where(['hall_name'=>$user_name]);
            }else if($hall_type == 2 && !empty($user_name))
            {//代理商数据
                $table->where(['agent_name'=>$user_name]);
            }
            $list =  $table->groupBy('day_month')->get()->toArray();
        }else
        {//天数据
            //生成近15天横坐标操作
            for($i=strtotime('-14 days');$i<=time();$i+=(3600*24))
            {
                $xAxis[] = date('Y-m-d',$i);
            }
            $table->select(DB::raw('sum(operator_win_score) as operator_win_score, sum(total_bet_score) as total_bet_score,DATE_FORMAT(add_date,\'%Y-%m-%d\') as add_day,day_day'));
            $oldDay = date('Y-m-d',strtotime('-14 days'));
            $table->where('add_date','>=',$oldDay);

            //判断是获取厅主数据还是代理商数据
            if($hall_type == 1 && !empty($user_name))
            {//厅主数据
                $table->where(['hall_name'=>$user_name]);
            }else if($hall_type == 2 && !empty($user_name))
            {//代理商数据
                $table->where(['agent_name'=>$user_name]);
            }
            $list =  $table->groupBy('day_day')->get()->toArray();
        }

        //进行数据组装
        foreach ($list as $key=>$val)
        {
            $xAxisDate = $type == 1 ? $val->add_month : $val->add_day;
            $key = array_search($xAxisDate,$xAxis);

            //组装盈利数据
            $operator_win_score['data'][$key] = sprintf("%.2f",$val->operator_win_score);
            //组装注单数据
            $total_bet_score['data'][$key] = sprintf("%.2f",$val->total_bet_score);
        }
        foreach ($xAxis as $k1=>$v1)
        {
            if(!isset($total_bet_score['data'][$k1]))
            {
                $total_bet_score['data'][$k1] = sprintf("%.2f",0);
            }
            if(!isset($operator_win_score['data'][$k1]))
            {
                $operator_win_score['data'][$k1] = sprintf("%.2f",0);
            }
        }

        ksort($operator_win_score['data']);
        ksort($total_bet_score['data']);
        $series = [$operator_win_score,$total_bet_score];
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


}