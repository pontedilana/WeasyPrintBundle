<?php

namespace Pontedilana\WeasyprintBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Process\ExecutableFinder;

class WeasyprintExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        if ($config['pdf']['enabled']) {
            $loader->load('pdf.php');

            $container->setParameter('weasyprint.pdf.binary', $this->resolveBinary($config['pdf']['binary']));
            $container->setParameter('weasyprint.pdf.options', $config['pdf']['options']);
            $container->setParameter('weasyprint.pdf.env', $config['pdf']['env']);

            if (!empty($config['temporary_folder'])) {
                $container->findDefinition('weasyprint.pdf')
                    ->addMethodCall('setTemporaryFolder', [$config['temporary_folder']]);
            }
            if (!empty($config['process_timeout'])) {
                $container->findDefinition('weasyprint.pdf')
                    ->addMethodCall('setTimeout', [$config['process_timeout']]);
            }
        }
    }

    /**
     * php-weasyprint verifies the binary with is_executable() before running it,
     * which fails for a bare command name (e.g. "weasyprint") because it is not
     * resolved against the PATH. Resolve it here so the convenient default keeps
     * working; an already-executable path is returned untouched.
     */
    private function resolveBinary(string $binary): string
    {
        if (is_executable($binary)) {
            return $binary;
        }

        return (new ExecutableFinder())->find($binary) ?? $binary;
    }
}
