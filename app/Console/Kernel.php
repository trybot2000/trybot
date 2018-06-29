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
        Commands\UpdateSlackEmojiList::class,
        Commands\GetCurrentTwitchStreams::class,
        Commands\FantasyFootballUpdateData::class,
        Commands\UpdateFortniteTrackerCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('slack:emoji')->hourly();
        $schedule->command('twitch:streams')->everyMinute();

        $schedule->command('fortnite:update')->cron('*/4  *  *  *  *');

        /**
         *
         *  Fantasy Football
         *
         */
        // During football times

/*
        $schedule->command('fantasy:update')->everyMinute()
            ->timezone('America/Chicago')
            ->sundays()
            ->between('8:00', '23:59')
            ->withoutOverlapping();

        $schedule->command('fantasy:update')->everyMinute()
            ->timezone('America/Chicago')
            ->mondays()
            ->between('17:00', '23:59')
            ->withoutOverlapping();

        $schedule->command('fantasy:update')->everyMinute()
            ->timezone('America/Chicago')
            ->thursdays()
            ->between('17:00', '23:59')
            ->withoutOverlapping();

        // Other times
        $schedule->command('fantasy:update')->everyTenMinutes()
            ->timezone('America/Chicago')
            ->weekdays()
            ->withoutOverlapping();
    */


    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
