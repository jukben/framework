<?php

namespace Marvin\Lib;

class SlackRequest
{
    public $token;
    public $teamDomain;
    public $channel;
    public $userName;
    public $botName;
    public $text;

    public function __construct(array $request)
    {
        $this->token      = $request['token'];
        $this->teamDomain = $request['team_domain'];
        $this->channel    = $request['channel_name'];
        $this->userName   = $request['user_name'];
        $this->botName    = $request['trigger_word'];
        $this->text       = $request['text'];
    }
}
