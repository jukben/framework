<?php

namespace Marvin\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class DisablePluginCommand extends BaseCommand
{
    protected function configure() {
        $this->setName("plugin:disable")
            ->setDescription("Disable a plugin")
            ->setHelp(<<<EOT
The <info>plugin:disable</info> command disables a plugin.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        // Load the plugins config file
        $configFile = sprintf('%s/config/plugins.php', PROJECT_DIR);
        $plugins = include($configFile);

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            "\n<question>Select the plugin to disable:</question>\n",
            $plugins
        );
        $question->setErrorMessage('Option %s is invalid.');

        $choice = $helper->ask($input, $output, $question);

        $key = array_search($choice, $plugins);
        unset($plugins[$key]);

        // Overwrite the config file
        $this->writeConfigFile($configFile, $plugins);

        $output->writeln(sprintf("\n<info>The plugin '%s' has been disabled</info>", $choice));
    }
}
