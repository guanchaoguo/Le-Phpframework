<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/4/6
 * Time: 9:42
 * 交收定时任务管理控制器
 */

namespace App\Http\Controllers\Admin\V1;

use App\Models\Agent;
use App\Models\Delivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class DeliveryCrontabController extends BaseController
{
    public static $scale;

    //监控
    public static function monitor()
    {
        //记录日志
        DB::connection('mongodb')->collection('system_log')->insert(['action_name'=>'交收定时任务','action_desc'=>'执行到了定时任务','action_passivity'=>'定时任务','user_id'=>1,'action_user'=>'sys','action_date'=>date("Y-m-d H:i:s",time()),'ip_info'=>'127.0.0.1']);
        $issueList = DeliveryController::getIssueData();

        if(!$issueList)
        {
            return false;
        }

        //时间判断，如果期数设置的时间小于或者大于当前时间，则进行数据的扫描统计操作，否则中断循环
        $setArray = [];
        foreach ($issueList as $key=>$val)
        {
            if(strtotime($val['end_date']) <= time())
            {
                $setArray[] = $val;
            }
            else
            {
                break;
            }
        }
        //获取所有厅主
        $agentList = Agent::where('grade_id','=',1)->where('is_hall_sub','=',0)->select('id')->get()->toArray();
        $agentIds = array_column($agentList,'id');
        //获取厅主的费用规则
        $scale = DB::table('game_platform_scale')->where(['state'=>1])->whereIn('p_id',$agentIds)->get()->toArray();
        //执行统计任务任务
        $issueArr = [];
        if(!empty($setArray))
        {
            foreach ($setArray as $k=>$v)
            {
                $issueArr[] = $v['issue'];
                if(!empty($v['start_date'])) {
                    $s_time = new \MongoDB\BSON\UTCDateTime(strtotime($v['start_date']) * 1000);
                    $match['start_time']['$gte'] = $s_time;
                }
                if(!empty($v['end_date'])) {
                    $e_time = new \MongoDB\BSON\UTCDateTime(strtotime($v['end_date'])  * 1000);
                    $match['start_time']['$lte'] = $e_time;
                }
                //按厅主分组
                $group = [
                    'hall_id' => '$hall_id',
                ];
                //显示的字段
                $field = [
                    'hall_id' =>1,
                    'hall_name'=> 1,
                    'operator_win_score'=>['$sum'  => '$operator_win_score']
                ];
                //获取厅主利润数据
                $res = GameStatisticsController::getUserChartInfo($group,$match,$field);
                $hallIds = array_column($res,'hall_id');
                //进行利润区间判断
                if(!empty($res))
                {
                    $insertData = [];
                    foreach ($scale as $sk=>$sv)
                    {
                        if(in_array($sv->p_id,$hallIds))
                        {
                            foreach ($res as $hall)
                            {
//                                if($hall['hall_id'] == $sv->p_id && ($sv->start_profit <= $hall['operator_win_score'] && ($hall['operator_win_score']< $sv->end_profit || $sv->end_profit == 0)))
                                if($hall['hall_id'] == $sv->p_id)
                                {
                                    //数据计算
                                    $cost = DB::table('game_platform_cost')->where(['p_id'=>$hall['hall_id']])->first();
//                                    $issueMoney = sprintf("%.2f", ($hall['operator_win_score'] * $sv->scale)/100);
                                    $issueMoney = self::compute($scale,$hall['operator_win_score'],$hall['hall_id']);
                                    $insertData[] = [
                                        'issue' => $v['issue'],
                                        'p_name'=> $hall['hall_name'],
                                        'p_id'  => $hall['hall_id'],
                                        'platform_profit' => sprintf('%.2f',$hall['operator_win_score']),
//                                        'scale' => self::$scale,
                                        'scale' => self::costStr($scale,$hall['hall_id']),
                                        'receipt'=> $issueMoney > 0 ? $issueMoney : 0.00,//为负数时，应交收费用为0
                                        'roundot'   => $cost->roundot,
                                        'line_map'  => $cost->line_map,
                                        'upkeep'    => $cost->upkeep,
                                        'ladle_bottom'  => $cost->ladle_bottom,
                                        'local_start_date'=> $v['local_start_date'],
                                        'local_end_date'=> $v['local_end_date']
                                    ];
                                }
                            }
                        }
                        else
                        {
                            //数据计算(没有利润数据的厅主)
                            $cost = DB::table('game_platform_cost')->where(['p_id'=>$sv->p_id])->first();
                            $agentname = Agent::where('id','=',$sv->p_id)->select('user_name')->first();
                            $issueMoney = 0.00;
                            $insertData[] = [
                                'issue' => $v['issue'],
                                'p_name'=> $agentname->user_name,
                                'p_id'  => $sv->p_id,
                                'platform_profit' => sprintf('%.2f',0.00),
//                                'scale' => $sv->scale,
                                'scale' => self::costStr($scale,$sv->p_id),
                                'receipt'=> $issueMoney,
                                'roundot'   => $cost->roundot,
                                'line_map'  => $cost->line_map,
                                'upkeep'    => $cost->upkeep,
                                'ladle_bottom'  => $cost->ladle_bottom,
                                'local_start_date'=> $v['local_start_date'],
                                'local_end_date'=> $v['local_end_date']
                            ];
                        }
                    }
                    //进行数据写入
                    if(!empty($insertData))
                    {

                        $insertData = self::remove_duplicate($insertData);
                        $insertState = DB::table('game_platform_delivery_info')->insert($insertData);
                    }

                    //数据添加成功信息期数状态修改为已扫描状态
                    if(isset($insertState))
                    {
                        Delivery::whereIn('issue',$issueArr)->update(['state'=>2]);
                    }

                }
                else
                {//本期没有利润数据时，统一按0计算
                    $insertData = [];
                    foreach ($scale as $sk=>$sv)
                    {
                                //数据计算
                                $cost = DB::table('game_platform_cost')->where(['p_id'=>$sv->p_id])->first();
                                $agentname = Agent::where('id','=',$sv->p_id)->select('user_name')->first();
                                $issueMoney = 0.00;
                                $insertData[] = [
                                    'issue' => $v['issue'],
                                    'p_name'=> $agentname->user_name,
                                    'p_id'  => $sv->p_id,
                                    'platform_profit' => sprintf('%.2f',0.00),
//                                    'scale' => $sv->scale,
                                    'scale' => self::costStr($scale,$sv->p_id),
                                    'receipt'=> $issueMoney,
                                    'roundot'   => $cost->roundot,
                                    'line_map'  => $cost->line_map,
                                    'upkeep'    => $cost->upkeep,
                                    'ladle_bottom'  => $cost->ladle_bottom,
                                    'local_start_date'=> $v['local_start_date'],
                                    'local_end_date'=> $v['local_end_date']
                                ];
                    }


                    //进行数据写入
                    if(!empty($insertData))
                    {
                        $insertData = self::remove_duplicate($insertData);
                       $insertState = DB::table('game_platform_delivery_info')->insert($insertData);
                    }

                    //数据添加成功信息期数状态修改为已扫描状态
                    if(isset($insertState))
                    {
                        Delivery::whereIn('issue',$issueArr)->update(['state'=>2]);
                    }
                }
            }
        }
    }

    public static function remove_duplicate($array)
    {
        $result=array();
        foreach ($array as $key => $value)
        {
            $has = false;
            foreach($result as $val){
                if($val['p_id']==$value['p_id']){
                    $has = true;
                    break;
                }
            }
            if(!$has)
                $result[]=$value;
        }
        return $result;
    }

    /**
     * 计算利润方法
     *  $data 为规则数组
     *  $$profit 为当前利润金额
     * */
    private static function compute($data = [],$profit = 0.00,$hall_id)
    {
        $receipt = 0.00;
        $profitMoeny = $profit;
        foreach ($data as $key=>$value)
        {
            if($key == 0)
            {
                if($hall_id == $value->p_id && $value->start_profit < $profit)
                {
                    if($value->end_profit > 0 && $profit >= $value->end_profit)
                    {
                        self::$scale = $value->scale;
                        $receipt += (($value->end_profit) * $value->scale) / 100;
                        $profitMoeny = $profit - $value->end_profit;//计算剩下需要交收的利润

                    }else
                    {
                        self::$scale = $value->scale;
                        $receipt += ($profitMoeny * $value->scale) / 100;
                        break;
                    }

                }
                else
                {
                    continue;
                }
            }else
            {
                if($hall_id == $value->p_id)
                {
                    if($value->end_profit > 0  && $profit >= $value->end_profit)
                    {
                        self::$scale = $value->scale;
                        $receipt += $profitMoeny > $value->end_profit-$value->start_profit ? (($value->end_profit-$value->start_profit) * $value->scale) / 100 : ($profitMoeny * $value->scale) / 100;
                        $profitMoeny = $profit - $value->end_profit; //计算剩下需要交收的利润

                    }else if ($value->end_profit == 0 ||  $profit < $value->end_profit )
                    {
                        self::$scale = $value->scale;
                        $receipt += ($profitMoeny * $value->scale) / 100;
                        break;
                    }

                }
                else
                {
                    continue;
                }
            }

        }
        return sprintf('%.2f',$receipt);
    }

    //游戏交收规则拼装
    public static  function costStr($scale,$hall_id)
    {
        $str = "<br/>";
        foreach ( $scale as $key=>$val)
        {
            if($val->p_id == $hall_id)
            {
                if($val->end_profit == 0)
                {
                    $str .= '&#8805;'.$val->start_profit." ： " .$val->scale."%" ."<br/>";
                }else
                {
                    $str .= $val->start_profit." - ".$val->end_profit ." ： " .$val->scale."%" ."<br/>";
                }
            }
        }
        return $str;
    }
}