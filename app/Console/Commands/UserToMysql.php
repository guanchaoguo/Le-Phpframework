<?php

/**
 * 外部玩家导入mysql
 * User: chensongjian
 * Date: 2017/7/21
 * Time: 10:00
 */
namespace App\Console\Commands;

use App\Models\CashRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UserToMysql extends Command
{
    /**
     * 命令名称标识
     * protected $commands = [
    \App\Console\Commands\AgentUserToMysql::class
     * ]
     * @var string
     */
    protected $signature = 'UserImportToMysql {userImport=lb_user_import} {limit=1000}';

    /**
     * 命令描述
     * @var string
     */
    protected $description = 'user import to mysql';

    //代理商原表
    const AGENT = 'lb_agent_user';
    //用户原表
    const USER = 'lb_user';
    //用户外部表
    const USER_IMPORT = 'lb_user_import';
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
        ini_set('memory_limit', '1024M');
        //外部输入导入表的参数
        $userImport = $this->argument('userImport');
        $pre_count = $this->argument('limit');//每次导入条数

        $db = DB::table($userImport);//要导入的外部玩家表

        $db2 = DB::table(self::USER);//导入到目标玩家表


        $counts = $db->count();//总计要导入的数
//        $pre_count = 1000;//每次导入条数
        $import_num = 0;//已导入条数
        $t1 = microtime(true);
        $count_users = [];

        for($i=0; $i <= intval($counts / $pre_count); $i++) {
            $offset = $i*$pre_count;
            $user = $db->offset($offset)->limit($pre_count)->get()->toArray();
            $import_num += count($user);
            foreach ($user as &$item) {

                $item = (array)$item;
                $hall_agent = DB::table(self::AGENT)->select('id')->where('user_name', $item['hall_name'])->first();
                $agent = DB::table(self::AGENT)->select(['id','agent_code'])->where('user_name', $item['agent_name'])->first();

                //将玩家的登录名，密码加密
                $item['user_name'] = decrypt_($agent->agent_code . $item['username_md']);
                $item['password'] = decrypt_($item['password']);
                $item['username_md'] = decrypt_($item['username_md']);
                $item['password_md'] = $item['password'];
                $item['create_time'] = $item['add_date'];
                $item['ip_info'] = $item['add_ip'];
                $item['password_mb_c'] && $item['password_mb_c'] = decrypt_($item['password_mb_c']);
                $item['password_mb_s'] && $item['password_mb_s'] = decrypt_($item['password_mb_s']);
                $item['alias'] = 'api会员';
                $item['hall_id'] = $hall_agent->id;
                $item['agent_id'] = $agent->id;

                unset($item['uid']);

                $count_users[$hall_agent->id] = isset($count_users[$hall_agent->id]) ? $count_users[$hall_agent->id] : 0;
                $count_users[$agent->id] = isset($count_users[$agent->id]) ? $count_users[$agent->id] : 0;

                $count_users[$hall_agent->id]  = $count_users[$hall_agent->id] + 1;
                $count_users[$agent->id]  = $count_users[$agent->id] + 1;

            };
            unset($item);
            //批量插入
            $db2->insert($user);

            $this->info('Has imported '.($import_num));
        }

        //玩家导入完成后，更新厅主、代理商表的玩家数
        foreach ($count_users as $k => $v) {

            DB::table(self::AGENT)->where('id',$k)->increment('sub_user', $v);
        }

        $t2 = microtime(true);
        $this->info('Took '.round($t2-$t1,2).' seconds');
        $this->info('Import is complete!');


    }


}
