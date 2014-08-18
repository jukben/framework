<?php

namespace Marvin\Lib;

use Marvin\Lib\Config;
use Marvin\Lib\SlackApi;

abstract class BasePlugin
{
    public $trigger;

    public $description;

    public $config = [];

    public $request;

    public function __construct($request)
    {
        $this->request = $request;

        // Set the trigger of the command
        $this->trigger();

        // Set the description of the command
        $this->description();

        // Set the config variables if the plugin has them
        if (method_exists($this, 'config')) {
            $this->config();
        }
    }

    public function addDescriptionLine($command, $description)
    {
        $this->description .= sprintf("*%s* - %s\n", $command, $description);
    }

    public function addConfigVariable($key, $value = null)
    {
        $this->config[$key] = $value;
    }

    public function getConfigVariable($key)
    {
        return Config::getPluginConfig('Weather', $key);
    }

    public function reply($text, $channel = null, $username = null, $icon = null)
    {
        $channel = !is_null($channel) ?: $this->request->channel;
        $channel = sprintf('%s%s', $channel[0] == '@' ?: '#', $channel);

        $username = !is_null($username) ?: $this->request->botName;

        if ($icon) {
            $body['icon_emoji'] = $icon;
        }

        // Get Slack API token from the config
        $token = Config::get('app.token');
        $icon = Config::get('app.icon');

        // Initialize Slack API
        $slackApi = new SlackApi($token, $icon);

        $slackApi->chatPostMessage($text, $channel, $username);
    }

    abstract function trigger();

    abstract function description();

    abstract function execute($parameters);
}
