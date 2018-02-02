<?php

/**
 * 创建测试用户、派彩注单、异常注单数据
 * User: chensongjian
 * Date: 2017/7/11/17
 * Time: 10:00
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\UserChartInfo;
use App\Models\ExceptionCashLog;

class CreateTestUsers extends Command
{
    /**
     * 命令名称标识
     * protected $commands = [
    \App\Console\Commands\AgentUserToMysql::class
     * ]
     * @var string
     */
    protected $signature = 'CreateTestUsers {name=index}';

    /**
     * 命令描述
     * @var string
     */
    protected $description = 'create test user';
    //用户表
    const USER = 'lb_user';
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
        $t1 = microtime(true);
        //外部输入导入表的参数
        $name = $this->argument('name');

        self::$name();
        //创建派彩数据1000万
        //self::createUserChartInfoData();
        //造异常注单数据
        //self::exception_cash_log();
       /* //创建用户
       $re = DB::table(self::USER)->where('agent_name','dlebo01')->where('alias','go_test_user')->first();
       if( $re ) {
           $this->info('the test user is have!');
       } else {
           $this->info('create test user...');
           self::createTestUser();
       }*/
        $t2 = microtime(true);
        $this->info('Took '.round($t2-$t1,2).' seconds');
        $this->info('create userChartInfo complete!');
    }

    public function index() {
        $this->info('请输入参数');
        $this->info('');
        $this->info('dlebo01TestUser : 创建用户（创建dlebo01代理商玩家数据）');
        $this->info('agentUsers : 创建不同代理商玩家500万条数据');
        $this->info('userChartInfo : 创建派彩数据2000万 （指定dlebo01代理）');
        $this->info('exceptionCashLog : 创建异常注单数据2000万（指定dlebo01代理）');
        $this->info('');
    }
    /**
     * 创建用户（创建dlebo01代理商玩家数据）
     */
    public function dlebo01TestUser(){
        $num = 2000;
        $data = [];
        for ($i=1; $i <= $num; $i++) {
            $username_md = "test_go_" . $i;
            $user_name = "D01" . $username_md;

            $tmp_data = [
                'user_name' => decrypt_($user_name),
                'username_md' => decrypt_($username_md),
                'password'=> decrypt_('111111'),
                'password_md' => decrypt_('111111'),
                'alias' => 'go_test_user',
                'create_time' => date('Y-m-d H:i:s'),
                'add_date' => date('Y-m-d H:i:s'),
                'hall_id' => 489,
                'hall_name' => 'tlebo01',
                'agent_id' => 490,
                'agent_name' => 'dlebo01',
            ];
            $data[] = $tmp_data;
        }
        DB::table(self::USER)->insert($data);
    }

    /**
     * 创建不同代理商玩家500万条数据
     */
    public function agentUsers(){

        $agentInfo = DB::table("lb_agent_user")->select('user_name','id','agent_code','parent_id')->where('grade_id', 2)->where('is_hall_sub',0)->where('account_type',1)->/*where('user_name','dlebo01')->*/get();
        $data = [];
        $h = 0;

        foreach ($agentInfo as $item) {

            $hallInfo = DB::table("lb_agent_user")->select('user_name','id')->find($item->parent_id);
            for ($i=1; $i <= 100000; $i++) {
                $username_md = "go_test_user" . $i;
                $user_name = $item->agent_code . $username_md;
                $tmp_data = [
                    'user_name' => decrypt_($user_name),
                    'username_md' => decrypt_($username_md),
                    'password'=> decrypt_('111111'),
                    'password_md' => decrypt_('111111'),
                    'alias' => 'go_test_user_2',
                    'create_time' => date('Y-m-d H:i:s'),
                    'add_date' => date('Y-m-d H:i:s'),
                    'hall_id' => $hallInfo->id,
                    'hall_name' => $hallInfo->user_name,
                    'agent_id' => $item->id,
                    'agent_name' => $item->user_name,
                ];
                $data[] = $tmp_data;
                $count = count($data);

                if($count == 5000) {
                    $h += $count;
                    DB::table('lb_user')->insert($data);
                    $this->info('has create user data :'.$h);
                    $data = [];
                }

            }

        }

    }

    /**
     * 创建派彩数据1000万 （指定dlebo01代理）
     */
    public function userChartInfo(){

        $c = 0;
        for ($j=0; $j <= 1000; $j++) {
            $data = [];
            for ($i=0; $i<=10000; $i++) {
                $date = self::rand_time('2017-01-01 00:00:00', '2017-11-24 00:00:00');
                $tmp_data = [
                    "game_round_id" => "5a169fdee1382379001ead6b",
                    "user_id" => $i,
                    "user_name" => "go_test_user".$i,
                    "chair_id" => 0,
                    "total_bet_score" => 500.0,
                    "total_win_score" =>  475.0,
                    "valid_bet_score_total" => 500.0,
                    "operator_win_score" => -475.0,
                    "hall_id" => 489,
                    "agent_id" => 490,
                    "game_id" => 91,
                    "cat_id" => 1,
                    "table_no" => 0,
                    "server_id" => 3,
                    "start_time" => new \MongoDB\BSON\UTCDateTime(strtotime($date) * 1000),
                    "occupancy_rate" =>  0.0,
                    "occupancy_rate_hall" => 0.0,
                    "occupancy_rate_hall" => 0.0,
                    "occupancy_rate_agent" => 0.0,
                    "server_name" => "3",
                    "is_cancel" => 0,
                    "round_no" => "bcccdcb2921b264c",
                    "game_period" => "179-65",
                    "dwRound" => 65,
                    "remark" => "6,50,0;50,42,0",
                    "account" => "go_test_user".$i,
                    "encry" => "MTc1NDczOCw1MDAsNDc1",
                    "is_mark" => 1,
                    "game_hall_id" => 0,
                    "game_hall_title" => "3",
                    "login_type" => 0,
                    "game_cat_code" => "GC0001",
                    "game_hall_code" => "GH0001",
                    "hall_name" => "tlebo01",
                    "agent_name" => "dlebo01",
                    "game_name" => "百家乐",
                    "game_result" => "8;2",
                    "ip_info" => "192.168.31.65",
                    "end_time" =>  new \MongoDB\BSON\UTCDateTime(strtotime($date) * 1000),
                ];

                $data[] = $tmp_data;


            }
            $c += 10000;
            UserChartInfo::insert($data);
            $this->info('has create user data :'.$c);

        }
    }

    /**
     * 造异常注单数据1000万（指定dlebo01代理）
     */
    public function exceptionCashLog() {
        $c = 0;
        for ($j=0; $j <= 1000; $j++) {
            $data = [];
            for ($i=0; $i<=10000; $i++) {
                $date = self::rand_time('2017-01-01 00:00:00', '2017-11-24 00:00:00');
                $tmp_data = [
                    "user_chart_id" => "59fac6a0e1382323c45fee03",
                    "order_sn" => "59fac6a0e1382323c45fee03",
                    "uid" => $i,
                    "user_name" => "go_test_user".$i,
                    "agent_id" => 490,
                    "agent_name" => "dlebo01",
                    "hall_id" => 489,
                    "hall_name" => "tlebo01",
                    "round_no" => "2e0fac7f9449eeb0",
                    "payout_win" => -40.0,
                    "before_user_money" => 198720.0,
                    "user_money" => 198760.0,
                    "bet_time" =>  new \MongoDB\BSON\UTCDateTime(strtotime($date) * 1000),
                    "desc" =>  "异常(取消)",
                    "action_user" => "chensj",
                    "action_user_id" => 1,
                    "action_passivity" => "2e0fac7f9449eeb0",
                    "add_time" =>  new \MongoDB\BSON\UTCDateTime(strtotime($date) * 1000),
                ];

                $data[] = $tmp_data;
            }
            $c += 10000;
            ExceptionCashLog::insert($data);
            $this->info('has create user data :'.$c);

        }
    }
    function rand_time($start_time,$end_time){
        $start_time = strtotime($start_time);
        $end_time = strtotime($end_time);
        return date('Y-m-d H:i:s', mt_rand($start_time,$end_time));
    }
}
