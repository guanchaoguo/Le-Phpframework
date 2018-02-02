<?php

/**
 * 造测试数据，来压测游戏api登录，充值，取款接口
 * User: chensongjian
 * Date: 2017/7/11/17
 * Time: 10:00
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateTestData extends Command
{
    /**
     * 命令名称标识
     * protected $commands = [
    \App\Console\Commands\AgentUserToMysql::class
     * ]
     * @var string
     */
    protected $signature = 'CreateTestData {name=index}';

    /**
     * 命令描述
     * @var string
     */
    protected $description = 'create test data';
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

        //生成测试数据
        self::$name();
//        self::createWhitelist();
//        self::createTestUser2();
        $t2 = microtime(true);
        $this->info('Took '.round($t2-$t1,2).' seconds');
        $this->info('create test data complete!');
    }

    public function index() {
        $this->info('请输入参数');
        $this->info('');
        $this->info('agentWhitelist : 创建厅主白名单数据');
        $this->info('dlebo01LoginDeposit : 创建测试游戏api充值、扣款，登录，测试数据（dlebo01代理商下的玩家）');
        $this->info('loginDeposit : 创建测试游戏api充值、扣款，登录，测试数据 （不同代理商不同玩家');
        $this->info('');
    }
    /**
     * 创建测试游戏api充值、扣款，登录，测试数据（dlebo01代理商下的玩家）
     */
    public function dlebo01LoginDeposit(){
        $num = 2000;
        $data = [];
        $agent_name = "dlebo01";
        $hall_name = "tlebo01";
        $amount = "0.01";
        $user_name_pre = "test_go_";

        $res = DB::table("white_list")->select("agent_seckey")->where("agent_name",$hall_name)->first();
        $agent_seckey = $res->agent_seckey;
        for ($i=1; $i <= $num; $i++) {
            $username = $user_name_pre . $i;
            $base_data = [
                'agent' => $agent_name,
                'username' => $username,
            ];
            //充值、扣款
            $deposit_withDrawal_data = $base_data;
            $deposit_withDrawal_data['amount'] = $amount;
            $deposit_withDrawal_data['token'] = sha1($agent_seckey . '|' .$username . '|' .$amount. '|' . $agent_name);
            $deposit_datas[$i] = $deposit_withDrawal_data;
            //登录
            $login_data = $base_data;
            $login_data['login_type'] = "1";
            $login_data['token'] = sha1($agent_seckey . '|' .$username . '|' . $agent_name .'|'.$login_data['login_type']);
            $login_datas[$i] = $login_data;

        }

        file_put_contents('test_login.json',json_encode($login_datas,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        file_put_contents('test_deposit_withDrawal.json',json_encode($deposit_datas,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    }

    /**
     * 创建厅主白名单数据
     */
    public function agentWhitelist(){
        $hallAgent = DB::table("lb_agent_user")->select('user_name','id')->where('grade_id', 1)->where('is_hall_sub',0)->get();
        $insert_data = [];
        foreach ($hallAgent as $item) {
            $str = str_shuffle($item->user_name.mt_rand(10,100000));
            $securityKey = createSecurityKey(env('SECURITY_KEY_ENCRYPT'),$str);
            $data = [
                'ip_info' => '*',
                'agent_id' => $item->id,
                'agent_name' => $item->user_name,
                'state' => 1,
                'agent_seckey' => $securityKey,
                'seckey_exp_date' => Carbon::parse('+'.env('KEY_MAX_VALID_TIME').' days')->toDateTimeString()
            ];
            $insert_data[] = $data;
        }

        DB::table("white_list")->delete();
        DB::table("white_list")->insert($insert_data);
    }

    /**
     * 造游戏api接口参数化数据 （不同代理商不同玩家）
     * 创建测试游戏api充值、扣款，登录，测试数据
     */
    public function loginDeposit(){

        $agentInfo = DB::table("lb_agent_user")->select('user_name','id','agent_code','parent_id')->where('grade_id', 2)->where('is_hall_sub',0)->where('account_type',1)->get();
        $deposit_datas = [];
        $login_datas = [];
        $j = 1;
        $amount = "0.01";
        $user_name_pre = "go_test_user";

        for ($i=1; $i <= 46; $i++) {
            foreach ($agentInfo as $item) {
                $res = DB::table("white_list")->select("agent_seckey")->where("agent_id",$item->parent_id)->first();
                $agent_seckey = $res->agent_seckey;
                $username = $user_name_pre . $i;
                $base_data = [
                    'agent' => $item->user_name,
                    'username' => $username,
                ];
                //充值、扣款
                $deposit_withDrawal_data = $base_data;
                $deposit_withDrawal_data['amount'] = $amount;
                $deposit_withDrawal_data['token'] = sha1($agent_seckey . '|' .$username . '|' .$amount. '|' . $item->user_name);
                $deposit_datas[$j] = $deposit_withDrawal_data;
                //登录
                $login_data = $base_data;
                $login_data['login_type'] = "1";
                $login_data['token'] = sha1($agent_seckey . '|' .$username . '|' . $item->user_name .'|'.$login_data['login_type']);
                $login_datas[$j] = $login_data;
                $j++;
            }
        }

        file_put_contents('test_login_2.json',json_encode($login_datas,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        file_put_contents('test_deposit_withDrawal_2.json',json_encode($deposit_datas,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    }
}
