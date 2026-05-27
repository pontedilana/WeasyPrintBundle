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

    /**
     * The container is dumped to a class named after the kernel/env/debug only, so
     * two kernels booted in the same process with different configuration would
     * reuse the first dumped container class. Vary the class name by configuration
     * so each distinct set of config files gets its own container.
     */
    protected function getContainerClass(): string
    {
        return parent::getContainerClass() . md5(implode(',', $this->configurationFilenames));
    }
}
