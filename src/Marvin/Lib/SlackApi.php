<?php

namespace Marvin\Lib;

class SlackApi
{
    public $baseUrl = 'https://slack.com/api';

    public $token;
    public $icon;

    public function __construct($token, $icon)
    {
        $this->token = $token;
        $this->icon  = $icon;
    }

    public function chatPostMessage($text, $channel = null, $username = null, $icon = null)
    {
        $parameters = array(
            'token' => $this->token,
            'channel' => $channel,
            'username' => $username,
            'text' => $text,
            'parse' => 'full',
            'pretty' => true
        );

        if (is_null($icon)) {
            $parameters['icon_url'] = $this->icon;
        }

        $url = sprintf('%s/%s?%s', $this->baseUrl, 'chat.postMessage', http_build_query($parameters));

        $client = new \GuzzleHttp\Client();
        $response = $client->get($url);
    }
}
