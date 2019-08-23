<?php
namespace App\Http\Controllers;

use App\Http\Controllers\ClassHelper;
use App\Http\Controllers\Slack\Helpers\Attachment;
use App\Http\Controllers\Slack\Helpers\Message;
use App\Http\Controllers\Slack\Slack;
use App\Http\Models\Twitch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Ixudra\Curl\Facades\Curl;
use \App\Exceptions\CurlTimeoutException;

class TwitchController extends ClassHelper
{
    protected $redis;
    protected $apiBaseUrl = "https://api.twitch.tv/helix";
    protected $clientId;

    public function __construct()
    {
        $this->redis    = new Redis();
        $this->clientId = env('TWITCH_API_CLIENT_ID', null);
    }

    /**
     * Get info about current streamers, with an optional timeout if time is a
     * factor.
     *
     * @param      int    $timeout  The timeout, in milliseconds (1s = 1000ms)
     *
     * @return     array  The array of streaming info. If no streamers, an empty array is returned.
     */
    public function getStreamers($timeout = 20)
    {
        $url = $this->apiBaseUrl . '/streams';

        // Get list of twitch usernames from the database
        $streams = Twitch::where('is_active', '=', '1')->get()->pluck('twitch_username');
        $streamsQuery = $streams->map(function($stream){
            return 'user_login='.$stream;
        });
        $url .= "?" . $streamsQuery->implode('&');

        $streamers = Curl::to($url)
            ->withTimeout($timeout)
            ->withHeader('Client-ID: ' . $this->clientId)
            ->returnResponseObject()
            ->asJson()
            ->get();

        // Get the string game for the given game_id
        collect($streamers->content->data)->map(function($streamer){
            $streamer->game = $this->getGameForGameId($streamer->game_id);
        });

        return $streamers->content->data;
    }

    public function getGameForGameId($gameId, $timeout = 20)
    {
        $url = $this->apiBaseUrl . '/games?id=' . $gameId;

        $game = Curl::to($url)
            ->withTimeout($timeout)
            ->withHeader('Client-ID: ' . $this->clientId)
            ->returnResponseObject()
            ->asJson()
            ->get();

        return collect($game->content->data)->first()->name;
    }

    /**
     * Get the User ID for a given username from the Twitch API
     *
     * @param      string       $username  The username to look up
     *
     * @return     string|null  The User ID returned from Twitch, or null if the user lookup wasn't successful
     */
    public function getUserIdFromUserName($username)
    {
        $url = $this->apiBaseUrl . "/users?" . http_build_query(['login' => $username]);

        $result = Curl::to($url)
            ->withHeader('Client-ID: ' . $this->clientId)
            ->returnResponseObject()
            ->asJson()
            ->get();

        // Parse the result for the user's ID
        return collect($result->content->data)
            ->pluck('id')
            ->first();
    }

    public function getNewlyStartedStreams()
    {
        $results = [];

        $streamers = collect($this->getStreamers());

        \Log::info("Current streams:" . $streamers->pluck('username')->implode(','));
        \Log::info($streamers);

        $slack = new Slack;

        // Loop over the streamers and see if they're already set in redis
        foreach ($streamers as $stream) {
            $redisKey                   = 'Twitch:Streaming:' . $stream->user_name;
            $results[$stream->user_name] = null;
            $s                          = Redis::get($redisKey);
            if (!$s) {
                // This is a new stream, so notify the channel
                $message = $this->buildTwitchMessage([$stream], false, false, true);

                \Log::info("Posing to channel");
                $results[$stream->user_name][] = $this->postMessage($message, $slack->casualChannelId, $stream->user_name);
                \Log::info("Message");
                \Log::info($message->build());

                // If it's a Destiny stream, also send to the Destiny channel
                if (preg_match('/destiny/i', $stream->game)) {
                    // This is a destiny stream (probably)
                    \Log::info("Posing to Destiny channel");
                    $results[$stream->user_name][] = $this->postMessage($message, $slack->channels['destiny'], $stream->user_name);
                }
                // If it's an Overwatch stream, also send to the Overwatch channel
                if (preg_match('/overwatch/i', $stream->game)) {
                    // This is an Overwatch stream (probably)
                    \Log::info("Posing to Overwatch channel");
                    $results[$stream->user_name][] = $this->postMessage($message, $slack->channels['overwatch'], $stream->user_name);
                }
                // If it's an Apex Legends stream, also send to the Apex channel
                if (preg_match('/apex legends/i', $stream->game)) {
                    // This is an Apex Legends stream (probably)
                    \Log::info("Posing to Apex channel");
                    $results[$stream->user_name][] = $this->postMessage($message, $slack->channels['apex'], $stream->user_name);
                }
                // If it's a Fortnite stream, also send to the Fortnite channel
                if (preg_match('/fortnite/i', $stream->game)) {
                    // This is a Fortnite stream (probably)
                    \Log::info("Posing to Fortnite channel");
                    $results[$stream->user_name][] = $this->postMessage($message, $slack->channels['fortnite'], $stream->user_name);
                }
                // If it's a CoD stream, also send to the Call of Duty channel
                if (preg_match('/call of duty/i', $stream->game)) {
                    // This is a CoD stream (probably)
                    \Log::info("Posing to CoD channel");
                    $results[$stream->user_name][] = $this->postMessage($message, $slack->channels['callofduty'], $stream->user_name);
                }
            } else {
                // Check that the current game matches the one we sent, and update if it's changed
                // TODO

                // Get the current game from redis
                $currentGame = Redis::get($redisKey);
                $newGame     = $stream->game ?: " ";
                // $newGame     = "Over Watch";
                \Log::info("currentGame: $currentGame");
                \Log::info("newGame: $newGame");
                if ($currentGame != $newGame) {
                    // The game has changed, so let's update the message we sent
                    $messageInfo = Redis::get('Twitch:Streaming:RecentMessages:' . $stream->user_name);
                    \Log::info("messageInfo: $messageInfo");
                    if ($messageInfo) {
                        // We have record of the message timestamp, which is required to update a message
                        $messageInfo = explode(":", $messageInfo);

                        // Build a message for the stream
                        // $stream->game = $newGame;
                        $message = $this->buildTwitchMessage([$stream], false, false, true);

                        // Update the existing message with the new one
                        \Log::info((array) $this->updateMessage($messageInfo[0], $message, $messageInfo[1], $stream->user_name));
                    }
                }
            }

            // Set the stream in redis, with a 6 minute expiration baked in, for all streams (which will just extend the time for current streams)
            Redis::setEx($redisKey, 60 * 6, $stream->game ?: " ");
        }
        return compact('streamers', 'results');
    }

    public function postMessage(Message $message, $channelId, $username = null)
    {
        // Send to Slack
        $slack    = new Slack;
        $response = $slack->postMessage($message, $channelId);

        // If there's an attachment in the message, log it so we can update the game
        // within a few minutes (if needed)
        if ($username) {
            $this->logTwitchPostForUpdate($response, $username);
        }

        return $response;
    }

    public function updateMessage($messageTs, Message $message, $channelId, $username = null)
    {
        // Send to Slack
        $slack    = new Slack;
        $response = $slack->updateMessage($messageTs, $message, $channelId);

        // If there's an attachment in the message, log it so we can update the game
        // within a few minutes (if needed)
        if ($username) {
            $this->logTwitchPostForUpdate($response, $username);
        }

        return $response;
    }

    public function logTwitchPostForUpdate($response, $username)
    {
        $response = collect($response->content);
        \Log::info($response->toJson());
        if ($response->get('ok') == 'true' && $response->get('ts') !== null && $response->get('channel') !== null) {
            // See if redis knows about this message, based on its timestamp
            $redisKey = 'Twitch:Streaming:RecentMessages:' . $username;
            $m        = Redis::get($redisKey);
            if (!$m) {
                // Log the message in redis, expiring in 10 minutes
                Redis::setEx($redisKey, 60 * 10, $response->get('ts') . ":" . $response->get('channel'));
            }
        }
    }

    public function buildTwitchMessage($streamers, $includePreamble = false, $includeViewerCount = true, $useLargePreviewImage = false, $includeMultiTwitch = false)
    {
        $message = new Message();
        $message->messageVisibleToChannel();

        $streamers = collect($streamers);

        if ($streamers->isEmpty()) {
            // No one is currently streaming
            $message->setText("Sorry, no one is currently streaming right now. If you just started, give Twitch a few minutes to let me know and try again!");
            $message->messageVisibleToChannel(false);
        } else {
            // Create a multitwitch URL
            $streamCollection = new Collection($streamers);
            $multitwitch      = "multitwitch.tv/" . $streamCollection->implode('username', '/');

            $preamble = null;
            if (count($streamers) === 1) {
                $preamble = "There is 1 person online! ";
            } else {
                $preamble = "There are " . count($streamers) . " people online! ";
            }

            if ($includePreamble) {
                $headers[] = $preamble;
            }

            if ($includeMultiTwitch === true && count($streamers) > 1) {
                $headers[] = $multitwitch;
            }

            if (!empty($headers)) {
                $message->setText(implode("\n\n", $headers));
            }

            foreach ($streamers as $k => $v) {
                $strViewers = "viewer" . ($v->viewer_count == 1 ? "" : "s");

                // Set the user/gamertag
                $strResponse = "*{$v->user_name}*";

                // Set streaming description and title (if it's set)
                if (empty($v->game)) {
                    $strResponse .= " is now streaming";
                } else {
                    $strResponse .= " is streaming " . "_" . $v->game . "_";
                }

                // Set number of viewers, if it should be included
                if ($includeViewerCount) {
                    if ($v->viewer_count > 0) {
                        $strResponse .= " to " . $v->viewer_count . " " . $strViewers;
                    }
                }

                $imageUrl = preg_replace('/\{width\}x\{height\}/i','640x360',$v->thumbnail_url);
                
                $a = new Attachment();
                $a->setUrl('https://twitch.tv/'.$v->user_name, $v->title);
                $a->setText($strResponse);
                \Log::info("image");
                \Log::info($imageUrl);
                $a->setImageURL($imageUrl);
                $a->processMarkdownForText();

                $message->addAttachment($a->build());
            }
        }
        return $message;
    }
}
