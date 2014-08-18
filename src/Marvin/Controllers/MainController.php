<?php

namespace Marvin\Controllers;

use Marvin\Lib\Config;
use Marvin\Lib\SlackRequest;
use Marvin\Lib\SlackApi;

class MainController
{
    /**
     * Handle the POST request
     *
     * @return string JSON response
     */
    public function postAction()
    {
        header('Content-type: application/json');

        $input = file_get_contents('php://input');
        parse_str($input, $inputArray);

        $request = new SlackRequest($inputArray);

        $botName = $request->botName;
        $username = $request->userName;
        $text = substr($request->text, strlen($botName) + 1);

        // Fetch the enables plugins from the config
        $plugins = Config::get('plugins');

        $command = null;
        $triggeredPlugin = null;

        foreach ($plugins as $plugin) {
            $obj = new $plugin($request);

            // Check if the command is for one of the triggers of the enabled plugins
            if (strpos(strtolower($text), strtolower($obj->trigger)) === 0) {
                $command = $obj->trigger;
                $triggeredPlugin = $obj;
                break;
            }
        }

        // If it's not a valid command check if it's the help command or return an error
        if (is_null($command)) {
            if (strpos(strtolower($text), 'help') === 0) {
                $command = 'help';
            } else {
                return json_encode(['text' => 'Command doesn\'t exist']);
            }
        }

        $parameterString = substr($text, strlen($command) + 1);
        $parameters = explode(' ', $parameterString);

        if ($command === 'help') {
            $helpText = sprintf("_Hi, my name is %s and I'm here to serve you. Just type my name and then one of the following commands:_\n\n", ucfirst($botName));
            foreach ($plugins as $plugin) {
                $obj = new $plugin($request);
                $helpText .= sprintf("%s\n", rtrim($obj->description, "\n"));
            }

            // Get Slack API token from the config
            $token = Config::get('app.token');
            $icon = Config::get('app.icon');

            // Send the reply as a private message
            $slackApi = new SlackApi($token, $icon);
            $slackApi->chatPostMessage($helpText, sprintf('@%s', $username), $botName);

            return null;
        } else {
            return $triggeredPlugin->execute($parameters);
        }
    }
}
