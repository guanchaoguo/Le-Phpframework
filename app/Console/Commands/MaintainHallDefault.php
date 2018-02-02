<?php
/**
 * Created by PhpStorm.
 * User: Sanji
 * Date: 2017/11/6
 * Time: 9:50
 * 厅主默认监控规则
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MaintainHallDefault extends Command
{
    /**
     * 命令名称标识
     * ps::要让该命令名称有效，需要在Kernel.php $commands数组加入该类路径
     * protected $commands = [
    \App\Console\Commands\MaintainHallDefault::class
     * ]
     * @var string
     */
    protected $signature = 'MaintainHallDefault';

    /**
     * 命令描述
     * @var string
     */
    protected $description = 'Maintain Hall  Default';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //获取所有厅主信息
        $hallList = DB::table("lb_agent_user")->where(["grade_id"=>1,"is_hall_sub"=>0])->select("id")->get()->toArray();

        //获取已经有规则的厅主
        $isHasRuleHallList = DB::table("sys_monitor")->where("hall_id",">",0)->select("hall_id")->get()->toArray();

        $monitor = DB::table("sys_monitor")->where("hall_id","=",0)->get()->toArray();
        //获取监控默认规则
        $defaultRule = DB::table("sys_monitor_rule")->where(["hall_id"=>0])->get()->toArray();

        $hallRuleList = DB::table("sys_monitor_rule")->get()->toArray();
        $monitorList = DB::table("sys_monitor")->get()->toArray();
        $M001List = DB::table("sys_monitor")->where(["tag"=>"M001"])->get()->toArray();



        $errorMsg = "";
        $toRedis = 0;
        //厅主数据写入
        if($hallList)
        {
            DB::beginTransaction();
            foreach ($hallList as $k=>$v)
            {
                if(!in_array($v->id,array_column($isHasRuleHallList,"hall_id")))
                {////进行mysql数据写入操作，并且进行redis数据同步更新操作
                    $insertMonitor = [];
                    foreach ($monitor as $kk=>$vv)
                    {
                        $insertMonitor[] = [
                            "hall_id" => $v->id,
                            "name"  => $vv->name,
                            "tag"   => $vv->tag,
                            "status" => 1  //默认为开启状态
                        ];
                    }
                   $res = DB::table("sys_monitor")->insert($insertMonitor);

                    $ruleData = [];
                    foreach ($defaultRule as $k2=>$v2)
                    {
                        $ruleData[] = [
                            "hall_id" => $v->id,
                            "tag" => $v2->tag,
                            "keycode" => $v2->keycode,
                            "value" => $v2->value,
                            "last_date" =>date("Y-m-d H:i:s",time())
                        ];
                    }
                    $res2 = DB::table("sys_monitor_rule")->insert($ruleData);


                    if(!$res || !$res2 )
                    {
                        $errorMsg = "操作失败";
                        break;
                        DB::rollBack();
                    }

                }
            }

            //进行redis同步更新操作
            $hallIdList = [];
            $tagList = [];
            foreach (StringShiftToInt($monitorList,["hall_id","status"]) as $k1=>$v1){
                $hallIdList[$v1->tag."-".$v1->hall_id] = $v1->status;
                $tagList[$v1->tag."-".$v1->hall_id] = $v1->tag;
            }

//            $tagList = array_unique($tagList);

            foreach (StringShiftToInt($hallRuleList,["hall_id","value"]) as $k2=>$v2)
            {
                    $hashData = [];
                    //redis 数据组装
                    $hashData[$v2->keycode] = $v2->value;
//                    $hashData['tag'] = $v2->tag;
                    $hashData['status'] = $hallIdList[$v2->tag."-".$v2->hall_id];
                    //写入到redis中
                    $redis = Redis::connection("monitor");
                    $redis->hMset(env('MONITOR_RULE').":".$v2->tag.":$v2->hall_id",$hashData);

            }

            //M001数据
            if(!empty($M001List))
            {
                foreach (StringShiftToInt($M001List,["status"]) as $k3=>$v3)
                {
                    $hashData = [];
//                    $hashData['tag'] = $v3->tag;
                    $hashData['status'] = $v3->status;
                    //写入到redis中
                    $redis = Redis::connection("monitor");
                    $redis->hMset(env('MONITOR_RULE').":".$v3->tag.":$v3->hall_id",$hashData);
                }
            }

            DB::commit();
            $errorMsg = "操作成功";
        }

        echo $errorMsg;
    }
}