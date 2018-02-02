<?php

/**
 * mysql现金流表导入mongodb
 * User: chensongjian
 * Date: 2017/7/21
 * Time: 9:35
 */
namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\CashRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CashRecordToMongodb extends Command
{
    /**
     * 命令名称标识
     * ps::mysql现金流导入mongodb
     * protected $commands = [
    \App\Console\Commands\CountUserOnline::class
     * ]
     * @var string
     */
    protected $signature = 'cashRecordImportToMongodb';

    /**
     * 命令描述
     * @var string
     */
    protected $description = 'cash record import to mongodb';
    //现金流外部表
    const CASH_RECORD_IMPORT = 'bak__cash_record_import';
    //用户原表
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
        $cash_record_mysql = DB::table(self::CASH_RECORD_IMPORT);//现金流记录表

        $counts = $cash_record_mysql->count();
        $this->info('Total:'.$counts);
        $pre_count = 1000;
        $import_num = 0;

        $t1 = microtime(true);

        for($i=0; $i <= intval($counts / $pre_count); $i++) {
            $offset = $i*$pre_count;
            $cash_record = $cash_record_mysql->offset($offset)->limit($pre_count)->get()->toArray();
            $import_num += count($cash_record);
            if($cash_record) {
                foreach ($cash_record as &$item) {
                    $item = (array)$item;
                    $item['add_time'] = new \MongoDB\BSON\UTCDateTime(strtotime($item['add_time']) * 1000);
                    $item['amount'] = (double) $item['amount'];
                    $item['user_money'] = (double) $item['user_money'];
                    $user_info = DB::table(self::USER)->select('uid', 'hall_id', 'agent_id')->where('user_name', decrypt_($item['user_name']))->first();
                    $item['uid'] = (int) $user_info->uid;
                    $item['agent_id'] = (int) $user_info->agent_id;
                    $item['hall_id'] = (int) $user_info->hall_id;
                    $item['desc'] = $item['desc'] ? $item['desc'] : '';
                    $item['cash_no'] = $item['order_sn'];
                    switch ($item['type']){
                        case 1:
                        case 4:
                            $item['desc'] = '流水号:'.$item['order_sn'];
                            break;
                        case 7:
                            $item['desc'] = '局'.$item['order_sn'].'派彩';
                            break;
                        case 21:
                        case 22:
                        case 23:
                        case 24:
                            $item['desc'] = '局'.$item['order_sn'].'下注';
                            break;

                    }

                    unset($item['id']);

                }
                unset($item);
                CashRecord::insert($cash_record);
                $this->info('Has imported '.$import_num);

            } else {
                break;
            }

        }
        $t2 = microtime(true);
        $this->info('Took '.round($t2-$t1,3).' seconds');
        $this->info('Import is complete!');

    }


}
