<?php

namespace App\Http\Controllers\Slack;

use \App\Exceptions\CurlTimeoutException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GoogleGeocoding;
use App\Http\Controllers\GoogleSearch;
use App\Http\Controllers\GoogleTimeZone;
use App\Http\Controllers\KnowledgeGraph;
use App\Http\Controllers\Slack\Helpers\Attachment;
use App\Http\Controllers\Slack\Helpers\Message;
use App\Http\Controllers\TwitchController;
use App\Http\Models\Twitch;
use App\Jobs\ReplyToTwitchCommand;
use App\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class Slash extends Controller
{

    public function twitch()
    {
        $payload = \Request::all();

        $twitchController = new TwitchController;

        // Get the user
        $user = User::where('slack_user_id', '=', $payload['user_id'])->first();
        \Log::info(json_encode($user));

        if (! $user) {
            // Create a new user
            \Log::info('Creating new user');
            $user = User::create(
                [
                'slack_user_name'   => $payload['user_name'],
                'slack_user_id'     => $payload['user_id'],
                'slack_team_id'     => $payload['team_id'],
                'slack_team_domain' => $payload['team_domain'],
                ]
            );
            $user->save();
            \Log::info('UserId: ' . $user->id);
            $user = User::find($user->id);
            \Log::info('Created user:');
            \Log::info(json_encode($user));
        }
        \Log::info('UserId: ' . $user->getId());

        if (isset($payload['user_id']) && $payload['user_id'] == 'U0662EN06') {
            // Sent by Jake
            // Feature switch for new functionality
            if (trim($payload['text']) == 'list all') {
                // Get all usernames
                \Log::info('/twitch list all');
                $longest = 0;
                $names   = Twitch::with('user')
                    ->get()
                    ->each(
                        function ($v, $k) use (&$longest) {
                            // Get longest twitch username length for fixed width later
                            \Log::info(strlen($v->twitch_username) . ' ' . $v->twitch_username);
                            if (strlen($v->twitch_username) > $longest) {
                                $longest = strlen($v->twitch_username);
                            }
                        }
                    )
                ->map(
                    function ($v, $k) use ($longest) {
                        \Log::info('longest: ' . $longest);
                        return '`' . str_pad($v->twitch_username, $longest) . ' => ' . $v->user->slack_user_name . '`';
                    }
                )
                  ->implode("\n");

                return $names;
            }
        }

        if (preg_match('/(help|commands?|\-\-help|\-h|\/\?)/i', $payload['text'], $matches)) {
            // It's a HELP command
            \Log::info('/twitch help');
            $r = "TryBot will automatically notify #casual when someone starts streaming, or when anyone sends `/twitch` in any channel. If you want your Twitch account to be included, use these commands to let TryBot know.\n\n";
            $r .= "Valid commands: set, list, delete (or nothing)\n";
            $r .= "  - `/twitch` returns the current streaming players (if any)\n";
            $r .= "  - `/twitch set [username]` tells TryBot your Twitch name\n";
            $r .= "  - `/twitch list` shows the Twitch name TryBot knows about\n";
            $r .= '  - `/twitch delete` makes TryBot forget about your Twitch name';

            $message = new Message();
            $message->messageVisibleToChannel();
            $message->setText($r);
            return response()->json($message->build());
        } elseif (preg_match('/(delete|del|remove)/i', $payload['text'], $matches)) {
            // It's a DELETE command, which removes the Twitch username set by this user
            \Log::info('/twitch delete');
            \Log::info('DELETE command');
            $user = User::find($user->getId());
            if ($user->twitch()->get()->isNotEmpty()) {
                // They have a username set
                $username = $user->getTwitchUsername();
                $user->twitch()->delete();
                // $user->save();

                return "I've removed *{$username}*! You can set your name using `/twitch set your_twitch_username`";
            } else {
                return "You didn't have a username set! You can set your name using `/twitch set your_twitch_username`";
            }
        } elseif (preg_match('/(list)/i', $payload['text'], $matches)) {
            // It's a LIST command
            \Log::info('/twitch list');
            \Log::info('LIST command');
            $user = User::find($user->getId());
            if ($user->twitch()->get()->isNotEmpty()) {
                // They have a username set
                return "You've told me to watch you on Twitch as *{$user->getTwitchUsername()}*";
            } else {
                return "You don't have a username set! You can set your name using `/twitch set your_twitch_username`";
            }
        } elseif (preg_match('/(set|add)(?:\s+([A-Za-z0-9_-]+))?/i', $payload['text'], $matches)) {
            // It's a SET command
            \Log::info('/twitch set');
            \Log::info('SET command');
            \Log::info('UserId: ' . $user->getId());
            // See if their user already has a Twitch username set
            $user = User::find($user->getId());
            \Log::info('User with twitch:');
            \Log::info(json_encode($user));
            \Log::info('Twitch:');
            \Log::info($user->twitch()->get());
            if ($user->twitch()->get()->isNotEmpty()) {
                // They already have a username set
                return "Sorry, you've already set your Twitch username to *{$user->getTwitchUsername()}*";
            }

            if (! isset($matches[2])) {
                // They only said "set"
                return 'If you want to set your Twitch gamertag, tell me by saying `/twitch set your_twitch_username`';
            }
            $username = trim($matches[2]);
            $twitch   = Twitch::where('twitch_username', '=', $username)->get();

            if (count($twitch) == 0) {
                // No Twitch usernames associated yet
                // Check that the username is correct in Twitch
                $twitchUserId = $twitchController->getUserIdFromUserName($username);

                Twitch::create(
                    [
                    'user_id'         => $user->getId(),
                    'twitch_username' => $username,
                    'twitch_user_id' => $twitchUserId, 
                    ]
                );
            }
            
            $username = Twitch::where('user_id', '=', $user->getId())->pluck('twitch_username')->first();
            return "Alright, I'll watch for you on Twitch as *{$username}*";
        }
        \Log::info('/twitch');

        // No commands, so just return the list of streamers
        $twitchController = new TwitchController;
        try {
            // Get the current list of streamers
            $streamers = $twitchController->getStreamers(2);
            $message   = $twitchController->buildTwitchMessage($streamers, true, true, true, true);
            \Log::info('returning directly');
        } catch (CurlTimeoutException $e) {
            // It took too long, so queue the response instead of responding directly
            $message = new Message();
            $message->messageVisibleToChannel();
            $message->setText('Let me check Twitch... Hang tight!');
            \Log::info('dispatching');
            dispatch(new ReplyToTwitchCommand($payload['response_url']));
        }

        return response()->json($message->build());
    }

    public function google()
    {
        $payload = \Request::all();
        \Log::info($payload);

        $message = new Message();
        $message->messageVisibleToChannel();

        $attachment = new Attachment();
        // $attachment->setColor("#2D4EB9");

        if (! isset($payload['text']) || is_null($payload['text'])) {
            return 'You need to actually search for something!';
        }
        $strSearchTerm = $payload['text'];

        $strResponse = 'http://lmgtfy.com/?q=' . urlencode($strSearchTerm);

        $knowledgeGraph       = new KnowledgeGraph();
        $knowledgeGraphResult = $knowledgeGraph->search($strSearchTerm);

        if ($knowledgeGraphResult['status'] == 'success') {
            if ($knowledgeGraphResult['data']['image'] != null) {
                $attachment->setImageURL($knowledgeGraphResult['data']['image']);
            }
            if (isset($knowledgeGraphResult['data']['detailedDescription']['articleBody'])) {
                $attachment->setText($knowledgeGraphResult['data']['detailedDescription']['articleBody']);
            } elseif (isset($knowledgeGraphResult['data']['description']) && isset($knowledgeGraphResult['data']['name'])) {
                $attachment->setText($knowledgeGraphResult['data']['name'] . ' (' . $knowledgeGraphResult['data']['description'] . ')');
            }
            $titleText = 'More Info';
            if (isset($knowledgeGraphResult['data']['name'])) {
                $titleText = $knowledgeGraphResult['data']['name'];
                if (isset($knowledgeGraphResult['data']['description'])) {
                    $titleText .= ' (' . strtolower($knowledgeGraphResult['data']['description']) . ')';
                }
            }
            $attachment->setUrl($knowledgeGraphResult['data']['moreInfoUrl'], $titleText);
            $message->addAttachment($attachment->build());
        } else {
            // Search regular google
            $googleSearch        = new GoogleSearch;
            $googleSearchResults = $googleSearch->search($strSearchTerm);
            \Log::info($googleSearchResults);

            if ($googleSearchResults && $googleSearch->isSuccess()) {
                // Parse the results into attachments and send that
                foreach ($googleSearchResults as $r) {
                    $a = new Attachment();
                    $a->setUrl($r['url'], $r['name']);
                    $a->setText(empty($r['description']) ? $r['description_alt'] : $r['description']);
                    $a->setThumbURL($r['image']);
                    $a->processMarkdownForText();

                    $message->addAttachment($a->build());
                }
            } else {
                return 'Here you go: http://google.com/search?q=' . urlencode($strSearchTerm);
            }
        }

        return response()->json($message->build());
    }

    public function tz()
    {
        $payload = \Request::all();
        \Log::info($payload);

        $message = new Message();
        $message->messageVisibleToChannel(false);

        if (! isset($payload['text']) || is_null($payload['text'])) {
            return 'You need to include a location!';
        }
        $location = $payload['text'];

        // Get the lat/lon
        $googleGeocoding = new GoogleGeocoding;
        $g               = $googleGeocoding->search($location);

        if (! isset($g['location']) || ! isset($g['latlon'])) {
            return "Sorry, something went wrong searching for $location";
        }

        // Get the time zone
        $googleTimeZone = new GoogleTimeZone;
        $t              = $googleTimeZone->search($g['latlon']);
        if (! isset($t['rawOffset'])) {
            return "Sorry, something went wrong searching for $location's time zone.";
        }

        $localTime = $googleTimeZone->getLocalTime();
        $message->setText('In ' . $g['location'] . " it's " . $localTime);
        $message->messageVisibleToChannel();

        return response()->json($message->build());
    }

    public function jizzMe()
    {
        $payload = \Request::all();
        \Log::info($payload);
        $message = new Message();
        $message->messageVisibleToChannel();

        $attachment = new Attachment();

        if (! isset($payload['text']) || is_null($payload['text'])) {
            $person1='';
            $person2='';
        } else {
            $text = $payload['text'];

            // Fix stupid quotes
            $text = str_replace(['“','”'], '"', $text);
            if (preg_match('/"?([^"]+)"?\s+"?([^"]+)"?/i', $text, $people)) {
                $person1=trim($people[1]);
                $person2=trim($people[2]);
            } else {
                $person1='';
                $person2='';
            }
        }

        if (strcasecmp($person1, 'trybot')==0 || strcasecmp($person2, 'trybot')==0) {
            $message->setText(':man-gesturing-no:');
        } else {
            $p1 = '';
            $p2 = '';
            if (strlen($person1)>0) {
                $p1=$person1 . ' -> ';
            }
            if (strlen($person2)>0) {
                $p2=' <- ' . $person2;
            }

            $message->setText($p1 . '8==:fist:D:sweat_drops:  :drooling_face:'.$p2);
        }

        return response()->json($message->build());
    }


    public function codes()
    {
        $numWords = [1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten'];

        $payload = \Request::all();
        \Log::info($payload);
        
        if (! isset($payload['text']) || is_null($payload['text'])) {
            $number = 5;
        } else {
            $number = intval($payload['text']);
            if ($number > 10 || $number < 1) {
                $number = 5;
            }
        }

        $message = new Message();
        $message->messageVisibleToChannel();

        // Get the 4 most recent unique codes
        $lastTenCodes = Redis::lrange('Slack:FNCreativeCodesList', 0, 9);
        $codes = collect($lastTenCodes)->unique()
            ->take($number)
            ->map(function ($code) {
                $titleRaw = html_entity_decode(Redis::get('Slack:FNCreativeCodesTitles:'. $code));
                $descriptionRaw = html_entity_decode(Redis::get('Slack:FNCreativeCodesDescriptions:'. $code));
                
                $title = preg_replace(['/&#x27;/'], ["'"], $titleRaw);
                $description = preg_replace(['/&#x27;/'], ["'"], $descriptionRaw);
                return "`${code}` " . ($title ? "*${title}*" : '') . ($description ? " - ${description}" : '');
            });

            $message->setText("Here's the last {$numWords[$number]} ". Str::plural('code', $number) . " mentioned in this channel:\n\n" . $codes->implode("\n"));

        return response()->json($message->build());
    }


    // public function template()
    // {
    //     $payload = \Request::all();
    //     \Log::info($payload);

    //     $message = new Message();
    //     $message->messageVisibleToChannel();

    //     $attachment = new Attachment();

    //     if (! isset($payload['text']) || is_null($payload['text'])) {
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
