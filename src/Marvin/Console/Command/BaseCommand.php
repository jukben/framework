<?php

namespace Marvin\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BaseCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new OutputFormatterStyle('cyan');
        $output->getFormatter()->setStyle('question', $style);
    }

    protected function writeConfigFile($file, $plugins)
    {
        $pluginsString = '';
        foreach ($plugins as $plugin) {
            $pluginsString .= sprintf("    '%s',\n", $plugin);
        }

        $content = sprintf("<?php\n\nreturn [\n%s];\n", $pluginsString);

        file_put_contents($file, $content);
    }
}
