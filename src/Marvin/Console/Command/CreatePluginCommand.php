<?php

namespace Marvin\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class CreatePluginCommand extends BaseCommand
{
    protected function configure() {
        $this->setName("plugin:create")
            ->setDescription("Create a workbench for developing a new plugin")
            ->setHelp(<<<EOT
The <info>plugin:create</info> creates a workbench for developing a new plugin.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $dialog = $this->getHelper('dialog');

        $vendor = $dialog->ask(
            $output,
            "\n<question>What is your vendor name? (e.g. Vendor)</question>: ",
            null
        );

        $plugin = $dialog->ask(
            $output,
            "<question>What is the name of your plugin? (e.g. PluginName)</question>: ",
            null
        );

        $description = $dialog->ask(
            $output,
            "<question>Description of your plugin</question>: ",
            null
        );


        $gitUsername = trim(`git config user.name`);
        $name = $dialog->ask(
            $output,
            sprintf("<question>What is you name?</question> [<comment>%s</comment>]: ", $gitUsername),
            $gitUsername
        );

        $gitEmail = trim(`git config user.email`);
        $email = $dialog->ask(
            $output,
            sprintf("<question>What is your email address?</question> [<comment>%s</comment>]: ", $gitEmail),
            $gitEmail
        );

        $vendorNamespace = $vendor;
        // Convert namespace to directory format
        $vendor = str_replace('\\', '/', $vendor);

        $workbenchDir = sprintf('%s/workbench', PROJECT_DIR);
        $pluginDir = sprintf('%s/src/%s', $workbenchDir, $vendor);

        $fs = new Filesystem();
        if ($fs->exists($pluginDir)) {
            $output->writeln(sprintf("\n<error>Workbench directory already exists</error>"));
        } else {
            try {
                $fs->mkdir($pluginDir);
            } catch (IOExceptionInterface $e) {
                $output->writeln(sprintf("\n<error>An error occurred while creating your directory at %s</error>", $e->getPath()));
            }
        }

        // Generate and create the base composer.json file for the new plugin
        $baseComposerFile = $this->generateBaseComposerFile($vendorNamespace, $plugin, $description, $name, $email);
        $fs->dumpFile(sprintf('%s/composer.json', $workbenchDir), $baseComposerFile);

        // Generate and create the base class
        $baseClass = $this->generateBaseClass($vendorNamespace, $plugin);
        $fs->dumpFile(sprintf('%s/%s.php', $pluginDir, $plugin), $baseClass);

        // Get the Marvin's composer file as a JSON object
        $composerFile = PROJECT_DIR . '/composer.json';
        $json = json_decode(file_get_contents($composerFile));

        // Add the chosen vendor to the psr-4 autoloader
        $json->autoload->{'psr-4'}->{sprintf("%s\\", $vendorNamespace)} = sprintf('workbench/src/%s', $vendor);

        // Write the updated composer JSON to the composer.json file
        $composerUpdated = json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        file_put_contents(PROJECT_DIR . '/composer.json', $composerUpdated);

        $output->writeln("\n<comment>Done</comment>");
    }

    private function generateBaseClass($vendor, $plugin)
    {
        $contents = <<<EOT
<?php

namespace $vendor;

use Marvin\Lib\BasePlugin;

class $plugin extends BasePlugin
{
    public function trigger()
    {
        \$this->trigger = 'YOUR_TRIGGER';
    }

    public function description()
    {
        \$this->addDescriptionLine(\$this->trigger, 'YOUR_DESCRIPTION');
    }

    public function config()
    {
        //\$this->addConfigVariable('KEY', 'VALUE');
    }

    public function execute(\$parameters)
    {
        return \$this->reply('YOUR_REPLY');
    }
}

EOT;
        return $contents;
    }

    private function generateBaseComposerFile($vendor, $plugin, $description, $name, $email)
    {
        $content = [
            'name' => sprintf('%s/%s', str_replace('\\', '-', strtolower($vendor)), strtolower($plugin)),
            'description' => $description,
            'authors' => [
                [
                    'name' => $name,
                    'email' => $email
                ]
            ],
            'require' => [
                'php' => '>=5.4.0'
            ],
            'autoload' => [
                'psr-4' => [
                    sprintf('%s\\', $vendor) => sprintf('src/%s', str_replace('\\', '/', $vendor))
                ]
            ]
        ];

        $json = json_encode($content, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

        return $json;
    }
}
