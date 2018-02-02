<?php

/**
 * Created by PhpStorm.
 * User: chensongjian
 * Date: 2017/6/13
 * Time: 9:35
 * 交收数据统计
 * 凌晨零点开始，每10分钟一次 *\/ 10 0 * * * /usr/bin/php /www/platform/artisan CountDelivery
 */
namespace App\Console\Commands;


use App\Http\Controllers\Admin\V1\DeliveryController;
use App\Http\Controllers\Admin\V1\GameStatisticsController;
use App\Models\Agent;
use App\Models\Delivery;
use App\Models\RedPacketsLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class CountDelivery extends Command
{
    public static $scale;

    /**
     * 命令名称标识
     * ps::要让该命令名称有效，需要在Kernel.php $commands数组加入该类路径
     * protected $commands = [
    \App\Console\Commands\CountDelivery::class,
     * ]
     * @var string
     */
    protected $signature = 'CountDelivery';

    /**
     * 命令描述
     * @var string
     */
    protected $description = 'count delivery';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    //监控
    public static function handle()
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
                $match['is_cancel'] = 0;
                if(!empty($v['start_date'])) {
                    $s_time = new \MongoDB\BSON\UTCDateTime(strtotime($v['start_date']) * 1000);
                    $match['start_time']['$gte'] = $s_time;
                    $redPakMatch['$match']["create_date"]['$lte'] = $s_time;
                }
                if(!empty($v['end_date'])) {
                    $e_time = new \MongoDB\BSON\UTCDateTime(strtotime($v['end_date'])  * 1000);
                    $match['start_time']['$lte'] = $e_time;
                    $redPakMatch['$match']["create_date"]['$lte'] = $e_time;
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

                $hallIds = array_column($res,'hall_id');//获取有下注记录的厅主ID集合
                //进行利润区间判断（有下注记录的厅主费用计算）
                if(!empty($res))
                {
                    $insertData = [];
                    foreach ($scale as $sk=>$sv) //遍历有设置交收规则的厅主信息
                    {
                        $redPakMatch['$match']['hall_id'] = (int)$sv->p_id;
                        //获取厅主的红包数据
                        $redPakField = [
                            '$project'=> [ 'packets_amount'=> 1]
                        ];
                        $redPakGroup = [
                            '$group' => [
                                '_id' => [ 'hall_id' => '$hall_id'],
                                'packets_amount'=>['$sum'=>'$packets_amount']
                            ]
                        ];

                        $aggregate = [$redPakMatch,$redPakField,$redPakGroup];
                        $redPakData = RedPacketsLog::raw(function($collection) use($aggregate) {
                            return $collection->aggregate($aggregate);
                        })->toArray();
                        //计算有下注记录的厅主费用
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
                                    $agentname = Agent::where('id','=',$sv->p_id)->select('user_name','real_name')->first();
                                    $insertData[] = [
                                        'issue' => $v['issue'],
                                        'p_name'=> $hall['hall_name'],
                                        'real_name' => $agentname->real_name,
                                        'p_id'  => $hall['hall_id'],
                                        'platform_profit' => sprintf('%.2f',$hall['operator_win_score']),
//                                        'scale' => self::$scale,
                                        'scale' => self::costStr($scale,$hall['hall_id']),
                                        'receipt'=> $issueMoney > 0 ? $issueMoney : 0.00,//为负数时，应交收费用为0
                                        'roundot'   => isset($cost->roundot) ? $cost->roundot : 0.00,
                                        'line_map'  => isset($cost->line_map) ? $cost->line_map : 0.00,
                                        'upkeep'    => isset($cost->upkeep) ? $cost->upkeep : 0.00,
                                        'ladle_bottom'  => isset($cost->ladle_bottom) ? $cost->ladle_bottom : 0.00,
                                        'local_start_date'=> $v['local_start_date'],
                                        'local_end_date'=> $v['local_end_date'],
                                        'red_packets' => isset($redPakData[0]["packets_amount"]) ? $redPakData[0]["packets_amount"] : 0.00
                                    ];
                                }
                            }
                        }
                        else
                        { //没有下注记录，只有包网费用的厅主费用计算
                            //数据计算
                            $cost = DB::table('game_platform_cost')->where(['p_id'=>$sv->p_id])->first();
                            $agentname = Agent::where('id','=',$sv->p_id)->select('user_name','real_name')->first();
                            $issueMoney = 0.00;
                            $insertData[] = [
                                'issue' => $v['issue'],
                                'p_name'=> $agentname->user_name,
                                'real_name' => $agentname->real_name,
                                'p_id'  => $sv->p_id,
                                'platform_profit' => sprintf('%.2f',0.00),
//                                'scale' => $sv->scale,
                                'scale' => self::costStr($scale,$sv->p_id),
                                'receipt'=> $issueMoney,
                                'roundot'   => isset($cost->roundot) ? $cost->roundot : 0.00,
                                'line_map'  => isset($cost->line_map) ? $cost->line_map : 0.00,
                                'upkeep'    => isset($cost->upkeep) ? $cost->upkeep : 0.00,
                                'ladle_bottom'  => isset($cost->ladle_bottom) ? $cost->ladle_bottom : 0.00,
                                'local_start_date'=> $v['local_start_date'],
                                'local_end_date'=> $v['local_end_date'],
                                'red_packets' => isset($redPakData[0]["packets_amount"]) ? $redPakData[0]["packets_amount"] : 0.00
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
                {   //（没有下注记录的厅主费用计算）
                    $insertData = [];
                    foreach ($scale as $sk=>$sv)
                    {
                        $redPakMatch['$match']['hall_id'] = (int)$sv->p_id;
                        //获取厅主的红包数据
                        $redPakField = [
                            '$project'=> [ 'packets_amount'=> 1]
                        ];
                        $redPakGroup = [
                            '$group' => [
                                '_id' => [ 'hall_id' => '$hall_id'],
                                'packets_amount'=>['$sum'=>'$packets_amount']
                            ]
                        ];

                        $aggregate = [$redPakMatch,$redPakField,$redPakGroup];
                        $redPakData = RedPacketsLog::raw(function($collection) use($aggregate) {
                            return $collection->aggregate($aggregate);
                        })->toArray();

                        //数据计算
                        $cost = DB::table('game_platform_cost')->where(['p_id'=>$sv->p_id])->first();
                        $agentname = Agent::where('id','=',$sv->p_id)->select('user_name','real_name')->first();
                        $issueMoney = 0.00;
                        $insertData[] = [
                            'issue' => $v['issue'],
                            'p_name'=> $agentname->user_name,
                            'real_name' => $agentname->real_name,
                            'p_id'  => $sv->p_id,
                            'platform_profit' => sprintf('%.2f',0.00),
//                            'scale' => $sv->scale,
                            'scale' => self::costStr($scale,$sv->p_id),
                            'receipt'=> $issueMoney,
                            'roundot'   => isset($cost->roundot) ? $cost->roundot : 0.00,
                            'line_map'  => isset($cost->line_map) ? $cost->line_map : 0.00,
                            'upkeep'    => isset($cost->upkeep) ? $cost->upkeep : 0.00,
                            'ladle_bottom'  => isset($cost->ladle_bottom) ? $cost->ladle_bottom : 0.00,
                            'local_start_date'=> $v['local_start_date'],
                            'local_end_date'=> $v['local_end_date'],
                            'red_packets' => isset($redPakData[0]["packets_amount"]) ? $redPakData[0]["packets_amount"] : 0.00
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
                $str .= $val->start_profit." - ".$val->end_profit ." ： " .$val->scale."%" ."<br/>";
            }
        }
        return $str;
    }
}