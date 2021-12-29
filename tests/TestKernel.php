<?php

namespace Pontedilana\WeasyprintBundle\Tests;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    private string $configurationFilename;

    /**
     * Defines the configuration filename.
     */
    public function setConfigurationFilename(string $filename): void
    {
        $this->configurationFilename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles(): array
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Pontedilana\WeasyprintBundle\WeasyprintBundle(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load($this->configurationFilename);
    }
}
