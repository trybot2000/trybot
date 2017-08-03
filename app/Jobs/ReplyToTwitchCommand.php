<?php

namespace App\Jobs;

use App\Http\Controllers\TwitchController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Ixudra\Curl\Facades\Curl;

class ReplyToTwitchCommand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $url;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $twitchController = new TwitchController;

        $streamers = $twitchController->getStreamers();

        $message = $twitchController->buildTwitchMessage($streamers, false, true, false, true);

        $postMessage = Curl::to($this->url)
            ->withData($message->build())
            ->asJson()
            ->returnResponseObject()
            ->post();

        \Log::info(json_encode($postMessage));
    }
}
