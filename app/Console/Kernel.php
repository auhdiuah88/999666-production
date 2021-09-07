<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        \App\Console\Commands\WbetGetResult::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command("CheckNewOrOld")
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();
        $schedule->command("Generate_Number")
            ->daily()
            ->withoutOverlapping()
            ->runInBackground();
//        $schedule->command("Mtb_Call")
//            ->everyTenMinutes()
//            ->withoutOverlapping()
//            ->runInBackground();
        $schedule->command("WbetGetResult")
            ->everyMinute();
        $schedule->command("WbetGetParlay")
            ->everyMinute();
        $schedule->command("WbetGetBet")
            ->everyMinute();
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
