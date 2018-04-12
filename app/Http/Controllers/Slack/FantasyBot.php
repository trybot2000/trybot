<?php

namespace App\Http\Controllers\Slack;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Slack\Helpers\Attachment;
use App\Http\Controllers\Slack\Helpers\Message;
use App\Jobs\CreateAndSendLeagueMatchupImage;
use App\Jobs\CreateAndSendNflGamesImage;
use App\Models\TeamsNotificationId;
use Facades\App\Services\FantasyFootball;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class FantasyBot extends Controller
{

    protected $checkChannel    = true;
    protected $maintenanceMode = false;
    protected $fantasyCommands = [
        'matchup', 'matchups', 'player',
    ];

    public function command(Request $request, $slashCommand)
    {
        $payload = \Request::all();

        if ($this->maintenanceMode === true) {
            if (array_get($payload, 'user_id') != 'U0662EN06') {
                return "I'm being fixed right now, be back up soon!";
            }
        }

        $command = strtolower(trim($slashCommand));
        if (method_exists(self::class, strtolower($command))) {
            // Check that this was called from a fantasy football channel (if required)
            if (in_array($command, $this->fantasyCommands)) {
                if (!$this->checkCurrentChannel(array_get($payload, "channel_id"))) {
                    return "Sorry, you can't use this command outside of a Fantasy Football channel!";
                }
            }
            return $this->$command($request);
        }
        abort(400);
    }

    public function checkCurrentChannel($channelId = null)
    {
        if ($this->checkChannel !== true) {
            return true;
        }

        // Get the list of channels for all tracked fantasy leagues
        $slackConfig = collect(config('espn.slack'));
        $channels    = $slackConfig->pluck('channelId');
        return $channels->contains($channelId);
    }

    public function matchup()
    {
        $payload = \Request::all();
        \Log::info($payload);

        $message = new Message();
        $message->messageVisibleToChannel(false);
        $message->setText("_Coming soon!_");

        $strMatchupImage = false;
        $teamId          = null;
        $leagueId        = null;

        // Get the leagueId based on configs
        $slackChannels = config('espn.slack');

        foreach ($slackChannels as $league => $channel) {
            if ($channel['channelId'] == $payload['channel_id']) {
                $leagueId = $league;
                break;
            }
        }

        // Update the league scoreboard
        FantasyFootball::updateLeagueScoreboard($leagueId);

        // Get the teamId for the user that called this command
        $team = TeamsNotificationId::where('slackUserId', '=', $payload['user_id'])->where('leagueId', '=', $leagueId)->first();

        if (!$team) {
            return "Sorry, I'm not sure who you are!";
        }

        // Optional "last" to get the prior matchup week
        $getPrior = preg_match('/\s*?(last)\s*?/i', $payload['text']);

        $teamId = $team->getTeamId();

        $matchupPeriod = FantasyFootball::getMatchupPeriodId($leagueId, $getPrior);
        \Log::info("Matchup period: $matchupPeriod");
        $matchup = FantasyFootball::getMatchup($leagueId, $teamId, $matchupPeriod);

        if ($matchup) {
            $message->messageVisibleToChannel(true);
            $message->setText(null);

            $attachment = new Attachment();
            $attachment->setImageUrl($matchup['imageUrl'] . "?r=" . \Helper::r());
            $attachment->setTs(Redis::get("FantasyFootball:log:updateLeagueScoreboard"));
            $message->addAttachment($attachment->build());

        }

        \Log::info($message->build());
        return response()->json($message->build());

    }

    public function matchups()
    {
        $payload = \Request::all();
        \Log::info($payload);

        $strMatchupImage = false;
        $leagueId        = null;

        $leagueId = $this->getLeagueByChannel($payload['channel_id']);

        // Optional "last" to get the prior matchup week
        $getPrior = preg_match('/\s*?(last)\s*?/i', $payload['text']);

        $message = new Message();
        $message->messageVisibleToChannel();
        \Log::info("dispatching response to /matchups");
        dispatch(new CreateAndSendLeagueMatchupImage($payload['response_url'], $leagueId, $getPrior));

        return response()->json($message->build());
    }

    public function player()
    {
        $payload = \Request::all();
        \Log::info($payload);

        $message = new Message();
        $message->messageVisibleToChannel(false);
        // $message->setText("_Coming soon!_");
        // return response()->json($message->build());

        $attachment = new Attachment();

        if (!isset($payload['text']) || is_null($payload['text'])) {
            return "You've gotta give me a player name!";
        }
        $text     = $payload['text'];
        $leagueId = $this->getLeagueByChannel($payload['channel_id']);

        $response = FantasyFootball::getPlayerStats($text, $leagueId);
        $message->setText($response);
        $message->messageVisibleToChannel();

        return response()->json($message->build());
    }

    public function nfl()
    {
        $payload = \Request::all();
        \Log::info($payload);

        $message = new Message();
        $message->messageVisibleToChannel();
        $liveGames = array_get($payload, 'text') == 'live';
        if ($liveGames) {
            // Check to see if any games are live before trying to create the image
            $games = FantasyFootball::getLiveNflGames();
            if (count($games) == 0) {
                $message->setText("Sorry, no games are live right now!");
                return response()->json($message->build());
            }
        }

        \Log::info("dispatching response to /nfl");
        dispatch(new CreateAndSendNflGamesImage($payload['response_url'], $liveGames));

        return response()->json($message->build());

    }

    public function schedule()
    {
        $payload = \Request::all();
        \Log::info($payload);

        $message = new Message();
        $message->messageVisibleToChannel(false);
        $message->setText("_Coming soon!_");
        return response()->json($message->build());
    }

    public function getLeagueByChannel($channelId)
    {
        // Get the leagueId based on configs
        $slackChannels = config('espn.slack');

        foreach ($slackChannels as $league => $channel) {
            if ($channel['channelId'] == $channelId) {
                return $league;
            }
        }
        return null;
    }

    // public function template()
    // {
    //     $payload = \Request::all();
    //     \Log::info($payload);

    //     $message = new Message();
    //     $message->messageVisibleToChannel(false);

    //     $attachment = new Attachment();

    //     if (!isset($payload['text']) || is_null($payload['text'])) {
    //         return "Response for empty input parameter";
    //     }
    //     $text = $payload['text'];

    //     // Add an attachment
    //     $attachment->setUrl('http://foo.com', 'foo.com');
    //     $attachment->setText("Text for the attachment");
    //     $attachment->setThumbURL('http://foo.com/i.png');
    //     $attachment->processMarkdownForText();

    //     $message->addAttachment($attachment->build());

    //     $message->setText("Reply from the command");

    //     return response()->json($message->build());
    // }

}
