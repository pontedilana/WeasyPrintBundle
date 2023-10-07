<?php

namespace Pontedilana\WeasyprintBundle\Tests;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    /** @var array<array-key, string> */
    private array $configurationFilenames = [];

    /**
     * @return BundleInterface[]
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
        foreach ($this->configurationFilenames as $filename) {
            $loader->load($filename);
        }
    }

    public function addConfigurationFilename(string $filename): void
    {
        $this->configurationFilenames[] = $filename;
    }
}
