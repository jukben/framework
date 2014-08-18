<?php

namespace Marvin\Lib;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class Config
{
    private static function getPath()
    {
        // Path to config directory is different if invoked from cli
        if (php_sapi_name() == "cli") {
            $path = sprintf('%s/config', getcwd());
        } else {
            $path = sprintf('%s/../config', getcwd());
        }

        return $path;
    }

    /**
     * Get a value from the config file using dot-notation
     *
     * @param  string $path The path to the value (e.g. 'weather.api_key')
     * @return mixed        The value of the key
     */
    public static function get($path)
    {
        // Get all the individual keys from the path
        $keys = explode('.', $path);

        // First element is the name of the config file
        $fileName = array_shift($keys);

        $config = include(sprintf('%s/%s.php', static::getPath(), $fileName));

        // Loop through the rest of the keys to get the value from the config
        while ($key = array_shift($keys)) {
            $config = &$config[$key];
        }

        return $config;
    }

    public static function publishPluginConfig($plugin, array $config)
    {
        $path = static::getPath();
        $configFile = sprintf('%s/%s/config.json', $path, $plugin);

        $fs = new Filesystem();

        // Don't overwrite if the config file already exists
        if (!$fs->exists($configFile)) {
            $fs->dumpFile($configFile, json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        }
    }

    public static function getPluginConfig($plugin, $key)
    {
        $path = static::getPath();
        $configFile = file_get_contents(sprintf('%s/%s/config.json', $path, $plugin));

        $config = json_decode($configFile);

        return $config->$key;
    }
}
