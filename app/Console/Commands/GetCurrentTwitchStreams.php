<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\TwitchController;

class GetCurrentTwitchStreams extends Command
{
    protected $signature = 'twitch:streams';
    protected $description = 'Get the current list of streamers on Twitch and notify the channel of new streams';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $twitch = new TwitchController();
        $twitch->getNewlyStartedStreams();
    }
}
