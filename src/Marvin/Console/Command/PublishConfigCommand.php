<?php

namespace Marvin\Console\Command;

use Marvin\Lib\Config;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PublishConfigCommand extends BaseCommand
{
    protected function configure() {
        $this->setName("config:publish")
            ->setDescription("Publish the config of the enabled plugins")
            ->setHelp(<<<EOT
The <info>config:publish</info> command publishes the config of the enabled plugins.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        // Fetch the enables plugins from the config
        $plugins = Config::get('plugins');

        foreach ($plugins as $plugin) {
            $obj = new $plugin(null);

            // Get the classname without the namespace
            $class = explode('\\', get_class($obj));
            $className = end($class);

            if (count($obj->config) > 1) {
                foreach ($obj->config as $key => $value) {
                    $config[$key] = $value;
                }

                Config::publishPluginConfig($className, $config);
            }
        }

        $output->writeln("\n<info>Done</info>");
    }
}
