<?php

namespace Marvin\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class ServeCommand extends BaseCommand
{
    protected function configure() {
        $this->setName("serve")
            ->setDescription("Serve the application using PHP's built-in web server")
            ->setHelp(<<<EOT
The <info>serve</info> command serves the application using PHP's built-in web server.
EOT
            )
            ->addOption(
               'port',
               null,
               InputOption::VALUE_REQUIRED,
               'The port to serve the application on.',
               8000
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf("\n<info>Development Server started on http://localhost:%s</info>\n", $input->getOption('port')));

        passthru(sprintf("php -S localhost:%s -t public/", $input->getOption('port')));
    }
}


