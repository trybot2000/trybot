<?php

namespace App\Http\Controllers\Slack;

use App\Http\Controllers\Controller;
use App\Http\Controllers\JsonResponse;
use App\Http\Controllers\Slack\Helpers\Message;
use App\Http\Models\Slack\Emoji;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Ixudra\Curl\Facades\Curl;

class Slack extends Controller
{

    public $tryBotUserIds = 'U4W8JMXEJ';

    public $botMentionTriggers = ['spaceybot', 'spacey bot', 'spacybot', 'spacy bot', 'trybot', 'trybot2000', 'try bot[^h]*?', 'trubot', 'tribot', 'tryboy', 'tri bot', 'hal9000', 'hal 9000', 'swedebot', 'swedebot9000', 'swede bot', 'spambot', 'wikibot', 'wiki bot', 'tryfuck'];

    public $casualChannelId  = "C0662FP5G";
    public $testingChannelId = "G1LKKBAQN";

    public $channels = [
        'casual'    => 'C0662FP5G',
        'testing'   => 'G1LKKBAQN',
        'destiny'   => 'G4VRPMSA2',
        'overwatch' => 'C4VPYMRDJ',
    ];

    public function event(\Illuminate\Http\Request $request)
    {
        Log::info($request->all());
        if (isset($request->event)) {
            // Get the type and log it
            \Log::info("Event type: " . $request->event['type']);
            $this->logEventType($request->event);

            if (isset($request->event['type'])) {
                if ($this->sentByABot($request->event) === false) {
                    // Sent by a human user
                    switch ($request->event['type']) {
                        case 'message':
                            $this->processMessage($request);
                            break;

                        // TODO: What other types of events might this receive??
                        // Check the different subtypes (if that key exists)
                        default:
                            # code...
                            break;
                    }
                }
            }
        }
    }

    public function getEventTypes()
    {
        $events = collect([]);
        $events->put('types', collect(Redis::zRange('Slack:EventLog:Types', 0, -1,"WITHSCORES")));
            $events->put('subtypes',collect(Redis::zRange('Slack:EventLog:Subtypes', 0, -1,"WITHSCORES")));
        return response()->collectionToHtmlTable($events);
    }

    public function logEventType($event)
    {
        if (isset($event['type'])) {
            Redis::zIncrBy('Slack:EventLog:Types', 1, $event['type']);
        }
        if (isset($event['subtype'])) {
            Redis::zIncrBy('Slack:EventLog:Subtypes', 1, $event['subtype']);
        }
    }

    public function processMessage($request)
    {
        if (isset($request->event['text'])) {
            $this->isSubreddit($request->event['text'], $request->event['channel']);
        }
    }

    public function sentByABot($event)
    {
        if (isset($event['subtype']) && $event['subtype'] == 'bot_message') {
            return true;
        }
        return false;
    }

    public function isSubreddit($text, $channel)
    {
        $pattern = "/(?<!reddit\.com|reddittryhard\.com)(?:(?:^|\/| )r\/){1}([\w]+)/i";
        if (preg_match($pattern, $text, $matches)) {
            $subName = $matches[1];
            // Search reddit for the sub, to make sure it's real and see if it's marked NSFW
            $redditSearchResult = json_decode(file_get_contents("http://www.reddit.com/subreddits/search.json?q=" . $subName), true);

            $topResult = false;

            foreach ($redditSearchResult['data']['children'] as $k => $v) {
                if ((trim(strtolower($v['data']['display_name'])) == trim(strtolower($subName))) && ($v['data']['subreddit_type'] === "public")) {
                    $topResult = $v['data'];
                    break;
                }
            }

            if ($topResult !== false) {
                // The first search result matches exactly!
                if ($topResult['over18'] === true) {
                    // It's a NSFW subreddit, so mark accordingly
                    $response = "Here's a link, but it's NSFW! Open carefully! reddit.com/r/" . $subName;
                } else {
                    $response = "Link for the lazy: reddit.com/r/" . $subName;
                }
            }

            $message = new Message();
            $message->messageVisibleToChannel();
            $message->setText($response);
            $this->postMessage($message, $channel);
        }
    }

    public function isMention($text)
    {
        $pattern = '/(\<\@' . $this->tryBotUserId . '\>|' . implode('|', $this->botMentionTriggers) . ')/i';
        if (preg_match($pattern, $text, $matches)) {
            return true;
        }
        return false;
    }

    public function postMessage(Message $message, $channel)
    {
        $baseUrl = "https://slack.com/api/chat.postMessage?";
        $token   = config('services.slack.users.trybot');
        if (!$token) {
            \Log::error('No Slack legacy token found in configs');
        }

        $message->setToken($token);
        if ($channel) {
            $message->setChannel($channel);
        }
        $url = $baseUrl . http_build_query($message->build(true));
        \Log::info($url);
        $postMessage = Curl::to($url)
            ->returnResponseObject()
            ->asJson()
            ->post();
        return $postMessage;
    }

    public function updateMessage($messageTs, Message $message, $channel)
    {
        \Log::info("Updating message $messageTs in channel $channel");
        $baseUrl = "https://slack.com/api/chat.update?";
        $token   = config('services.slack.users.trybot');
        if (!$token) {
            \Log::error('No Slack legacy token found in configs');
        }

        $message->setToken($token);
        if ($channel) {
            $message->setChannel($channel);
        }
        $url = $baseUrl . http_build_query($message->build(true));
        \Log::info($url);
        $postMessage = Curl::to($url)
            ->returnResponseObject()
            ->post();
        return $postMessage;
    }

    public function getUser($userId)
    {

    }

    public function getEmojiList()
    {
        \Log::info("Getting emoji list");
        $tStart = microtime(true);

        $redis = new Redis();
        $token = config('services.slack.legacy_token');
        if (!$token) {
            \Log::error('No Slack legacy token found in configs');
        }

        $url = "https://slack.com/api/emoji.list?token=" . $token;

        $emojiListData = json_decode(file_get_contents($url), true);
        $emojiList     = $emojiListData['emoji'];

        // Get a list of all current emoji in the database
        $currentEmoji = Emoji::all();
        $data         = [
            'added'     => 0,
            'deleted'   => 0,
            'new_files' => 0,
            'aliases'   => 0,
            'emoji'     => 0,
            'total'     => 0,
            'list'      => [],
        ];

        // Loop over existing emoji and mark any that no longer exist as inactive
        foreach ($currentEmoji as $emoji) {
            if (!in_array($emoji->getName(), array_keys($emojiList))) {
                $emoji->setInactive();
                $data['deleted'] += 1;
            } else {
                if (!$emoji->isActive()) {
                    // Existed before, now re-activated (instead of being added)
                    $data['added'] += 1;
                }
                $emoji->setActive();
            }
            $emoji->save();
        }

        $data['list'] = $emojiList;

        // Loop over all emoji and store their existence in the database (including updated URLs)
        // Also save a copy of the image in /public/img/emoji
        foreach ($emojiList as $k => $v) {
            $newEmoji = Emoji::firstOrNew(['name' => $k]);
            $isAlias  = false;

            $data['total'] += 1;
            if (!$newEmoji->exists) {
                $data['added'] += 1;
            }

            if (preg_match('/^alias\:(.*)/i', $v, $aliasInfo)) {
                // This is an alias
                $newEmoji->setAlias($aliasInfo[1]);
                $isAlias = true;
                $data['aliases'] += 1;
            }
            $newEmoji->setUrl($v);
            $newEmoji->setActive();
            $newEmoji->save();

            if (!$isAlias) {
                // Download and save the image
                $data['emoji'] += 1;

                $pathInfo = pathinfo($v);
                $filename = public_path() . '/img/emoji/' . $k . '_' . $pathInfo['basename'];
                if (!file_exists($filename)) {
                    $img = file_get_contents($v);
                    file_put_contents($filename, $img);
                    $data['new_files'] += 1;
                }
            }

        }
        $tComplete = round(microtime(true) - $tStart, 2);
        \Log::info("Finished processing emoji list");
        \Log::info("Took $tComplete seconds");
        \Log::info($data);
        return JsonResponse::success($data);

    }
}
