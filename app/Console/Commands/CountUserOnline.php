<?php

/**
 * Created by PhpStorm.
 * User: chensongjian
 * Date: 2017/6/13
 * Time: 9:35
 * 玩家在线统计，昨日和今日在线玩家数据统计
 * 凌晨零点开始，每10分钟一次 *\/ 10 0 * * * /usr/bin/php /www/platform/artisan CountUserOnline
 */
namespace App\Console\Commands;

use App\Models\Player;
use App\Models\StatisOnlineUser;
use Carbon\Carbon;
use App\Models\Agent;
use Illuminate\Console\Command;

class CountUserOnline extends Command
{
    /**
     * 命令名称标识
     * ps::要让该命令名称有效，需要在Kernel.php $commands数组加入该类路径
     * protected $commands = [
            \App\Console\Commands\CountUserOnline::class
     * ]
     * @var string
     */
    protected $signature = 'CountUserOnline';

    /**
     * 命令描述
     * @var string
     */
    protected $description = 'count user online';

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

        //今天开始时间
        $todayStartTime = Carbon::now()->startOfDay()->toDateTimeString();
        $nowTime = Carbon::now()->format('Y-m-d H:i');

        $onlineUser = StatisOnlineUser::select(
            \DB::raw('DATE_FORMAT(add_date,"%Y-%m-%d %H:%i") as add_date'),
            'date_scale'
        )->where('add_date', '>=', $todayStartTime)->orderby('id','desc')->first();

        if( $onlineUser ) {

            $add_date = (new Carbon($onlineUser['add_date']))->addMinute(10)->timestamp;
            $nowtimestamp = Carbon::now()->timestamp;
            //每隔十分钟插入数据一次
            if( $nowtimestamp <  $add_date ){
                //echo ' Execute once every ten minutes';
                $this->info('Execute once every ten minutes-'.$nowTime);
                return;
            }

        }
        //要对得上刻度值才插入
        $scale = config('scale.'.Carbon::now()->format('Hi'));
        if( ! $scale ) {
//            echo ' The scale is not correct';
            $this->info('The scale is not correct:'.Carbon::now()->format('Hi').'-'.$nowTime);
            return;
        }
        //获取测试、联调厅主id
        $ids = Agent::where([ 'grade_id' => 1, 'is_hall_sub' => 0])->whereIn('account_type',[2,3])->pluck('id');
        //统计玩家今天在线的人数
        $count = Player::select('uid')->where('on_line','Y')->whereNotIn('hall_id',$ids)->where('last_time', '>=', $todayStartTime)->count();

        $inser_data = [
            'online_user' => $count,
            'date_scale' => $scale,
            'add_date' => $nowTime,
            'date_year' => Carbon::now()->year,
            'date_month' => Carbon::now()->month,
            'date_day' => Carbon::now()->day,
        ];
        //入库处理
        $re = StatisOnlineUser::insert($inser_data);
        if( $re ) {
            $this->info('CountUserOnline Success-'.$nowTime);
        } else {
            $this->info('CountUserOnline Error-'.$nowTime);
        }

    }
}