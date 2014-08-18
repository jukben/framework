<?php

namespace Marvin\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

define('PROJECT_DIR', __DIR__.'/../../../..');

class BaseCommand extends Command
{
    protected function execute($input, $output)
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
