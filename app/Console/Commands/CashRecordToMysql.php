<?php

/**
 * mongodb现金流表导入mysql
 * User: chensongjian
 * Date: 2017/7/21
 * Time: 10:00
 */
namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\CashRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CashRecordToMysql extends Command
{
    /**
     * 命令名称标识
     * ps::mysql现金流导入mysql
     * protected $commands = [
            \App\Console\Commands\CountUserOnline::class
     * ]
     * @var string
     */
    protected $signature = 'cashRecordImportToMysql';

    /**
     * 命令描述
     * @var string
     */
    protected $description = 'cash record import to mysql';

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

        $cash_record_mysql = DB::table('bak__cash_record');
        $counts = CashRecord::count();
        $pre_count = 3000;
        for($i=0; $i < intval($counts / $pre_count); $i++) {
            $offset = $i*$pre_count;
            $cash_record = CashRecord::offset($offset)->limit($pre_count)->get();

            $cash_record->each(function ($item) {
                $item['add_time'] = date('Y-m-d H:i:s',$item['add_time']->__tostring()/1000);
                if( !isset($item['hall_id']) ) {
                    $item['hall_id'] = 0;
                }
                if( !isset($item['agent_id']) ) {
                    $item['agent_id'] = 0;
                }
                unset($item['_id']);
            });

            $cash_record_mysql->insert($cash_record->toArray());
            $this->info('Import '.($offset+$pre_count));
        }

        $this->info('Import Success');


    }


}