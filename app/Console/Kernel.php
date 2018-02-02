<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CountDelivery::class,
        \App\Console\Commands\CashRecordToMongodb::class,
        \App\Console\Commands\AgentUserToMysql::class,
        \App\Console\Commands\UserToMysql::class,

        \App\Console\Commands\CountUserOnline::class,
        \App\Console\Commands\ActiveUser::class,
        \App\Console\Commands\CountDelivery::class,
        \App\Console\Commands\CashRecordToMysql::class,
        \App\Console\Commands\CashRecordToMongodb::class,
        \App\Console\Commands\AgentUserToMysql::class,
        \App\Console\Commands\UserToMysql::class,
        \App\Console\Commands\NewUserName::class,
        \App\Console\Commands\CreateTestData::class,
        \App\Console\Commands\CreateTestUsers::class,
        \App\Console\Commands\MaintainHallDefault::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
