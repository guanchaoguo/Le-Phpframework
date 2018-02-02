<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/6/2
 * Time: 13:55
 *  活跃会员定时任务
 *  每小时执行一次
 */
namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\Controller;
use App\Models\UserChartInfo;
use Illuminate\Support\Facades\DB;

class ActiveUserCrontabController extends Controller
{

    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 执行活跃会员数据统计任务
     *++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 基本业务思路：当天有投注记录的会员标识为活跃会员
     * 执行任务时获取会员注单数据，以代理商进行分组，然后记录表中
     * 每60分钟获取一次数据，后面的数据覆盖以前的活跃会员记录数据
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    public function run()
    {
        //获取当天的时间
        $start_date = strtotime("2017-04-25");
        //$start_date = strtotime('-4 days');
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