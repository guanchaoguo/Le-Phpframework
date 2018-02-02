<?php

/**
 * 用户名加密算法修改，需要更新用户名加密方式
 * User: chensongjian
 * Date: 2017/7/11/13
 * Time: 10:00
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NewUserName extends Command
{
    /**
     * 命令名称标识
     * protected $commands = [
    \App\Console\Commands\AgentUserToMysql::class
     * ]
     * @var string
     */
    protected $signature = 'NewUserName';

    /**
     * 命令描述
     * @var string
     */
    protected $description = 'new user name ';
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

        $db = DB::table(self::USER);//目标玩家表
        $counts = $db->count();//总计
        $pre_count = 1000;//每次更新条数
        $import_num = 0;//已更新条数
        $t1 = microtime(true);
        for($i=0; $i <= intval($counts / $pre_count); $i++) {
            $offset = $i*$pre_count;
            $user = $db->select("uid","user_name","password","username_md","password_md")->offset($offset)->limit($pre_count)->get()->toArray();
            $import_num += count($user);
            foreach ($user as &$item) {
                $item = (array)$item;
                $item["user_name"] = $item["user_name"] ? decrypt_(encrypt_old($item["user_name"])) : "";
                $item["password"] = $item["password"] ? decrypt_(encrypt_old($item["password"])) : "";
                $item["username_md"] = $item["username_md"] ? decrypt_(encrypt_old($item["username_md"])) : "";
                $item["password_md"] = $item["password_md"] ? decrypt_(encrypt_old($item["password_md"])) : "";
            };
            self::updateBatch($user);
            $this->info('Has update '.($import_num));
        }

        $t2 = microtime(true);
        $this->info('Took '.round($t2-$t1,2).' seconds');
        $this->info('update is complete!');


    }


    //批量更新
    public function updateBatch($multipleData = [])
    {
        try {
            if (empty($multipleData)) {
                throw new \Exception("数据不能为空");
            }
            $tableName = self::USER; // 表名
            $firstRow  = current($multipleData);

            $updateColumn = array_keys($firstRow);

            // 默认以id为条件更新，如果没有ID则以第一个字段为条件
            $referenceColumn = isset($firstRow['uid']) ? 'uid' : current($updateColumn);

            unset($updateColumn[0]);
            // 拼接sql语句
            $updateSql = "UPDATE " . $tableName . " SET ";
            $sets      = [];
            $bindings  = [];
            foreach ($updateColumn as $uColumn) {
                $setSql = "`" . $uColumn . "` = CASE ";
                foreach ($multipleData as $data) {
                    $setSql .= "WHEN `" . $referenceColumn . "` = ? THEN ? ";
                    $bindings[] = $data[$referenceColumn];
                    $bindings[] = $data[$uColumn];
                }
                $setSql .= "ELSE `" . $uColumn . "` END ";
                $sets[] = $setSql;
            }
            $updateSql .= implode(', ', $sets);
            $whereIn   = collect($multipleData)->pluck($referenceColumn)->values()->all();
            $bindings  = array_merge($bindings, $whereIn);
            $whereIn   = rtrim(str_repeat('?,', count($whereIn)), ',');
            $updateSql = rtrim($updateSql, ", ") . " WHERE `" . $referenceColumn . "` IN (" . $whereIn . ")";
//            var_export($updateSql);
//            var_export($bindings);die;
            // 传入预处理sql语句和对应绑定数据
            return DB::update($updateSql, $bindings);
        } catch (\Exception $e) {
            return false;
        }
    }

}
