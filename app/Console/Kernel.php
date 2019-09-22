<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // 'App\Console\Commands\getNews',
        // 'App\Console\Commands\CreateSiteMap',

        'App\Console\Commands\cronJobGetExchange',
        'App\Console\Commands\cronJobGetInterestRate',
        'App\Console\Commands\cronJobGetGold',
        'App\Console\Commands\cronJobVirtualMoney',
        'App\Console\Commands\cronJobGetOilPetro'
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('sitemap:create')->hourly();

        //cron providers
        // $schedule->command('getExchanges:getBankdata')->cron('30 7,8,9,10,11,12,14,15,16,18,20,22 * * *');
        // $schedule->command('getNews:NewsTyGia')->hourly();

        $schedule->command('cronJob:getExchanges')->cron('30 7,8,9,10,11,12,14,15,16,18,20,22 * * *');
        $schedule->command('cronJob:getInterestRate')->cron('* 8,9,10,15,16 * * *');
        $schedule->command('cronJob:getGold')->cron('30 7,8,9,10,11,12,14,15,16,18,20,22 * * *');
        $schedule->command('cronJob:getVirtualMoney')->hourly();
        $schedule->command('cronJob:getOilPetro')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
