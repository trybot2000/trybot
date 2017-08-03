<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\TwitchController;

class GetCurrentTwitchStreams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitch:streams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the current list of streamers on Twitch and notify the channel of new streams';

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
        $twitch = new TwitchController();
        $twitch->getNewlyStartedStreams();
    }
}
