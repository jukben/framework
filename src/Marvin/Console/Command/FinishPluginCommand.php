<?php

namespace Marvin\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class FinishPluginCommand extends BaseCommand
{
    protected function configure() {
        $this->setName("plugin:finish")
            ->setDescription("Finish development of a plugin")
            ->setHelp(<<<EOT
The <info>plugin:finish</info> command enables a plugin.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        // Ask the user if they want to remove the workbench directory
        $dialog = $this->getHelper('dialog');

        $repoUrl = $dialog->ask(
            $output,
            "\n<question>What is the url of your repository? (e.g. git@github.com:the-marvin-bot/your-repo.git)</question>: ",
            null
        );

        $version = $dialog->ask(
            $output,
            "<question>What version number should be used to tag this release?</question> [<comment>1.0.0</comment>]: ",
            "1.0.0"
        );

        $workbenchDir = sprintf('%s/workbench', PROJECT_DIR);

        $commands = [
            sprintf('cd %s', $workbenchDir),
            'git init',
            'git add .',
            'git commit -m "Initial commit"',
            sprintf('git remote add origin %s', $repoUrl),
            sprintf('git tag %s -m "Initial release"', $version),
            'git push -q -u origin --all',
            'git push -q -u origin --tags',
        ];

        exec(implode(' && ', $commands), $execOutput, $returnCode);

        if ($returnCode !== 0) {
            foreach ($execOutput as $line) {
                $output->writeln($line);
            }
            $output->writeln("\n<error>Error</error>");
        } else {
            $output->writeln(sprintf("\n<info>Plugin successfully comitted</info>"));

            $answer = $dialog->ask(
                $output,
                "\n<question>Do you want to remove the workbench directory?</question> [<comment>y/n</comment>]: ",
                null
            );

            if (strtolower($answer) === 'y') {
                $fs = new Filesystem();
                if ($fs->exists($workbenchDir)) {
                    $fs->remove($workbenchDir);
                    $output->writeln(sprintf("\n<info>Workbench directory removed</info>"));
                }

                // Get the Marvin's composer file as a JSON object
                $composerFile = PROJECT_DIR . '/composer.json';
                $json = json_decode(file_get_contents($composerFile));

                $psr4 = $json->autoload->{'psr-4'};
                // Go to the last element of the psr-4 object
                end($psr4);
                // Get the key of the last element
                $key = key($psr4);
                // Unset the last element
                unset($psr4->$key);

                // Overwrite the psr4 object
                $json->autoload->{'psr-4'} = $psr4;

                // Write the updated composer JSON to the composer.json file
                $composerUpdated = json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                file_put_contents(PROJECT_DIR . '/composer.json', $composerUpdated);
            }
            $output->writeln("\n<comment>Done</comment>");
        }

    }
}
