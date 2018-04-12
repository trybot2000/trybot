<?php

namespace App\Http\Controllers\Slack\Helpers;

/**
 *  Message object helper
 */
class Message
{

    public $token;
    public $channel;
    public $text;
    public $parse;
    public $link_names;
    public $attachments;
    public $unfurl_links;
    public $unfurl_media;
    public $username;
    public $as_user;
    public $icon_url;
    public $icon_emoji;
    public $thread_ts;
    public $reply_broadcast;

    public $ts;

    // Only for things like responses to slash commands
    public $response_type;

    public function __construct($user = 'trybot')
    {
        $token = config('services.slack.users.' . $user);
    }

    public function build($prepareForQuery = false)
    {
        $vars    = get_class_vars(get_class($this));
        $payload = [];
        $json    = "";

        foreach ($vars as $k => $v) {
            if (is_null($this->{$k})) {
                continue;
            }

            $payload[$k] = $this->{$k};
        }
        if ($prepareForQuery === true) {
            // Format the attachments value as json_encoded text, rather than an array, which prepares it for http_build_query
            if (!empty($payload['attachments'])) {
                $payload['attachments'] = json_encode($payload['attachments']);
            }
        }
        return $payload;
    }

    public function setUpdateMessageTs($value)
    {
      $this->ts = $value;
    }

    public function setToken($value)
    {
        $this->token = $value;
    }

    public function setChannel($value)
    {
        $this->channel = $value;
    }

    public function setText($value)
    {
        $this->text = $value;
    }

    public function setParse($value)
    {
        $this->parse = $value;
    }

    public function setLinkNames($value)
    {
        $this->link_names = $value;
    }

    public function setAttachments(array $value)
    {
        $this->attachments = $value;
    }

    public function addAttachment($attachment)
    {
        $this->attachments[] = $attachment;
    }

    public function setUnfurlLinks($value)
    {
        $this->unfurl_links = $value;
    }

    public function setUnfurlMedia($value)
    {
        $this->unfurl_media = $value;
    }

    public function setUsername($value)
    {
        $this->username = $value;
    }

    public function setAsUser($value)
    {
        $this->as_user = $value;
    }

    public function setIconURL($value)
    {
        $this->icon_url = $value;
    }

    public function setIconEmoji($value)
    {
        $this->icon_emoji = $value;
    }

    public function setThreadTs($value)
    {
        $this->thread_ts = $value;
    }

    public function setReplyBroadcast($value)
    {
        $this->reply_broadcast = $value;
    }

    public function setResponseType($value)
    {
        $this->response_type = $value;
    }

    public function messageVisibleToChannel($value = true)
    {
        if ($value) {
            $this->response_type = 'in_channel';
        } else {
            $this->response_type = 'ephemeral';
        }
    }

}
