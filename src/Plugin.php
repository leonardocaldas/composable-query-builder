<?php

namespace ComposableQueryBuilder;

use Composer\Plugin\PluginInterface;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\Capability\CommandProvider;

class Plugin implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        // Plugin activation logic
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // Optional: Plugin deactivation logic
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // Optional: Plugin uninstall logic
    }
}
