<?php

namespace App\Jobs;

use App\Http\Controllers\Slack\Helpers\Attachment;
use App\Http\Controllers\Slack\Helpers\Message;
use Facades\App\Services\FantasyFootball;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Ixudra\Curl\Facades\Curl;

class CreateAndSendNflGamesImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $url;
    protected $liveGames;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url, $liveGames = false)
    {
        $this->url       = $url;
        $this->liveGames = $liveGames;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // Get NFL game data

        $matchup = FantasyFootball::createNflMatchupImage($this->liveGames);

        $message = new Message();
        if ($matchup) {
            $message->messageVisibleToChannel();

            $attachment = new Attachment();
            $attachment->setImageUrl("https://trybot2000.com/img/ff/" . $matchup . "?r=" . \Helper::r());
            $attachment->setTs(Redis::get("FantasyFootball:log:updateLeagueScoreboard"));
            $message->addAttachment($attachment->build());

            \Log::info(json_encode($message->build()));

            $postMessage = Curl::to($this->url)
                ->withData($message->build())
                ->asJson()
                ->returnResponseObject()
                ->post();

            \Log::info(json_encode($postMessage));

        }

    }
}
