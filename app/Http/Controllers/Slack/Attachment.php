<?php

namespace App\Http\Controllers\Slack;

/**
 *  Attachment payload helper
 */
class Attachment
{

    protected $fallback;
    public $color;
    protected $pretext;
    protected $author_name;
    protected $author_link;
    protected $author_icon;
    protected $title;
    protected $title_link;
    protected $text;
    protected $fields;
    protected $image_url;
    protected $thumb_url;
    protected $footer;
    protected $footer_icon;
    protected $ts;

    public function build()
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
        return $payload;
    }

    public function setUrl($url, $urlTitle = null)
    {
        $this->setTitleLink($url);
        if (!is_null($urlTitle)) {
            $this->setTitle($urlTitle);
        } else {
            $this->setTitle($url);
        }
    }

    public function setFallback($value)
    {
        $this->fallback = $value;
    }

    public function setColor($value)
    {
        $this->color = $value;
    }

    public function setPretext($value)
    {
        $this->pretext = $value;
    }

    public function setAuthorName($value)
    {
        $this->author_name = $value;
    }

    public function setAuthorLink($value)
    {
        $this->author_link = $value;
    }

    public function setAuthorIcon($value)
    {
        $this->author_icon = $value;
    }

    public function setTitle($value)
    {
        $this->title = $value;
    }

    public function setTitleLink($value)
    {
        $this->title_link = $value;
    }

    public function setText($value)
    {
        $this->text = $value;
        if (is_null($this->fallback)) {
            $this->setFallback($value);
        }
    }

    public function setFields($value)
    {
        $this->fields = $value;
    }

    public function setImageURL($value)
    {
        $this->image_url = $value;
    }

    public function setThumbURL($value)
    {
        $this->thumb_url = $value;
    }

    public function setFooter($value)
    {
        $this->footer = $value;
    }

    public function setFooterIcon($value)
    {
        $this->footer_icon = $value;
    }

    public function setTs($value)
    {
        $this->ts = $value;
    }

    public function setReplyBroadcast($value)
    {
        $this->reply_broadcast = $value;
    }


}
