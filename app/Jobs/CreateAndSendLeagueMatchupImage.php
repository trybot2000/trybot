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

class CreateAndSendLeagueMatchupImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $url;
    protected $leagueId;
    protected $getPrior;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url, $leagueId, $getPrior)
    {
        $this->url      = $url;
        $this->leagueId = $leagueId;
        $this->getPrior = $getPrior;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Update the league scoreboard
        // FantasyFootball::updateLeagueScoreboard($this->leagueId);

        // Get the matchup period
        $matchupPeriod = FantasyFootball::getMatchupPeriodId($this->leagueId, $this->getPrior);

        $matchups = FantasyFootball::createAllMatchupImages($this->leagueId, false, $matchupPeriod);

        $updatedAt = Redis::get("FantasyFootball:log:updateLeagueScoreboard");

        $message = new Message();
        \Log::info($matchups);
        if ($matchups) {
            $message->messageVisibleToChannel(true);
            // $message->setText(null);

            $attachment = new Attachment();
            $attachment->setText("");
            $attachment->setImageUrl("https://trybot2000.com/img/ff/$matchups?r" . \Helper::r());
            $attachment->setTs($updatedAt);

            $message->addAttachment($attachment->build());

            \Log::info($message->build());

            $postMessage = Curl::to($this->url)
                ->withData($message->build())
                ->asJson()
                ->returnResponseObject()
                ->post();

            \Log::info(json_encode($postMessage));

        }

    }
}
