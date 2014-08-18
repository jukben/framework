<?php

namespace Marvin\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EnablePluginCommand extends BaseCommand
{
    protected function configure() {
        $this->setName("plugin:enable")
            ->setDescription("Enable a plugin")
            ->setHelp(<<<EOT
The <info>plugin:enable</info> command enables a plugin.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        // Ask the user what plugin they want to have enabled
        $dialog = $this->getHelper('dialog');
        $plugin = $dialog->ask(
            $output,
            "\n<question>Please enter the name of the plugin you want to enable (e.g. Marvin\Plugins\Weather)</question>: ",
            null
        );

        // Load the plugins config file
        $configFile = sprintf('%s/config/plugins.php', __DIR__.'/../../../..');
        $plugins = include($configFile);

        // Add the new plugin to the plugins array
        $plugins[] = $plugin;

        // Overwrite the config file
        $this->writeConfigFile($configFile, $plugins);

        $output->writeln(sprintf("\n<info>The plugin '%s' has been enabled</info>", $plugin));
    }
}
