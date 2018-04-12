<?php

namespace App\Console\Commands;

use App\Services\FantasyFootball;
use Illuminate\Console\Command;

class FantasyFootballUpdateData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fantasy:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Various scheduled updates and tasks for fantasy football';

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
        $f = new FantasyFootball;

        \Log::info("Updating NFL schedule");
        $f->updateNflSchedule();
        $f->updateNflBroadcastInfo();
        \Log::info("");

        \Log::info("Updating fantasy football data");
        $f->updateAllLeagues();
        \Log::info("");

        \Log::info("Processing notifications");
        $f->processPlayerStatusChangeNotifications();
        $f->processPlayerProTeamIdNotifications();
        $f->processTransactionNotifications();

        \Log::info("DONE!");
    }
}
