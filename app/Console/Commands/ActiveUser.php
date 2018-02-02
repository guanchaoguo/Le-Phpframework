<?php

/**
 * Created by PhpStorm.
 * User: chensongjian
 * Date: 2017/6/13
 * Time: 9:35
 * 活跃玩家统计
 * 凌晨零点开始，每10分钟一次 *\/ 10 0 * * * /usr/bin/php /www/platform/artisan ActiveUser
 */
namespace App\Console\Commands;


use App\Models\UserChartInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Models\Agent;

class ActiveUser extends Command
{
    /**
     * 命令名称标识
     * ps::要让该命令名称有效，需要在Kernel.php $commands数组加入该类路径
     * protected $commands = [
            \App\Console\Commands\ActiveUser::class
     * ]
     * @var string
     */
    protected $signature = 'ActiveUser';

    /**
     * 命令描述
     * @var string
     */
    protected $description = 'count active user';

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
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 执行活跃会员数据统计任务
     *++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 基本业务思路：当天有投注记录的会员标识为活跃会员
     * 执行任务时获取会员注单数据，以代理商进行分组，然后记录表中
     * 每60分钟获取一次数据，后面的数据覆盖以前的活跃会员记录数据
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    public function handle()
    {
        DB::connection('mongodb')->collection('system_log')->insert(['action_name'=>'活跃玩家定时任务','action_desc'=>'执行到了活跃玩家定时任务','action_passivity'=>'定时任务','user_id'=>1,'action_user'=>'sys','action_date'=>date("Y-m-d H:i:s",time()),'ip_info'=>'127.0.0.1']);

        //获取当天的时间
        //$start_date = strtotime('-4 days');
        $start_date = strtotime(date("Y-m-d"));
        $end_date = $start_date + 24*60*60;

        //按代理商id分组
        $group = [
            'agent_id',
        ];

        $field = [
            'hall_id',
            'agent_id',
            'hall_name',
            'agent_name',
        ];

        if(isset($start_date) && !empty($start_date)) {
            $s_time = new \MongoDB\BSON\UTCDateTime($start_date * 1000);
            $match['start_time']['$gte'] = $s_time;
        }

        if(isset($end_date) && !empty($end_date)) {
            $e_time = new \MongoDB\BSON\UTCDateTime($end_date* 1000);
            $match['start_time']['$lte'] = $e_time;
        }
        //获取测试、联调代理id
        $ids = Agent::where(['grade_id' => 2, 'is_hall_sub' => '0'])->whereIn('account_type',[2,3])->pluck('id')->toArray();
        $match['agent_id']['$nin'] = $ids;
        //代理商分组数据
        $agentList = $this->getUserCharInfo($group,$match,$field);
        //会员分组注单数据
        $userGroup = [
            'user_id',
        ];
        $userField = ['agent_id'];
        $userList = $this->getUserCharInfo($userGroup,$match,$userField);
        $agentActiveUserList = array_count_values(array_column($userList,'agent_id'));
        $insertData = "";
        if($agentList && $agentActiveUserList)
        {
            foreach ($agentList as $key=>$val)
            {
                $pkId =  ($val['agent_id'] * 10) + date('d');
                $insertData .= "("
                    .$pkId.','.//主键生成规则，由代理商ID乘以10 再加上当天的天数，保证唯一性
                    $val['hall_id'].','.
                    '\''.$val['hall_name'].'\','.
                    $val['agent_id'].','.
                    '\''.$val['agent_name'].'\','.
                    '\''.date("Y-m-d H:i:s").'\','.
                    date('Y').','.
                    date('m').','.
                    date('d').','.
                    $agentActiveUserList[$val['agent_id']]
                    ."),";
            }
            $insertData = rtrim($insertData,',');
            //进行数据更新写入操作
            $res = DB::update(" replace INTO statis_active_user(id,hall_id,hall_name,agent_id,agent_name,add_time,date_year,date_month,date_day,active_user)
  VALUES $insertData");
        }

    }

    private function getUserCharInfo($group,$where,$field)
    {
        return UserChartInfo::select($field)->where($where)->groupBy($group)->get()->toArray();
    }
}