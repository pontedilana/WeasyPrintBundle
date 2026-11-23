<?php

namespace Pontedilana\WeasyprintBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

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

            $container->setParameter('weasyprint.pdf.binary', $this->resolveBinary($container, $config['pdf']['binary']));
            $container->setParameter('weasyprint.pdf.options', $config['pdf']['options']);
            $container->setParameter('weasyprint.pdf.env', $config['pdf']['env']);
            $container->setParameter('weasyprint.pdf.allowed_schemes', [] === $config['pdf']['allowed_schemes'] ? null : $config['pdf']['allowed_schemes']);

            if (!empty($config['temporary_folder'])) {
                $container->findDefinition('weasyprint.pdf')
                    ->addMethodCall('setTemporaryFolder', [$config['temporary_folder']]);
            }
            $processTimeout = $config['process_timeout'] ?? null;
            if (false === $processTimeout) {
                $container->findDefinition('weasyprint.pdf')
                    ->addMethodCall('disableTimeout', []);
            } elseif (!empty($processTimeout)) {
                $container->findDefinition('weasyprint.pdf')
                    ->addMethodCall('setTimeout', [$processTimeout]);
            }
        }
    }

    /**
     * A binary written in the configuration is resolved against the PATH now. A binary
     * made of a single env var is only known at runtime, so it is handed over to
     * BinaryEnvVarProcessor, which resolves it when the service is created.
     */
    private function resolveBinary(ContainerBuilder $container, string $binary): string
    {
        $usedEnvs = [];
        $resolved = $container->resolveEnvPlaceholders($binary, '%%env(%s)%%', $usedEnvs);
        $envs = array_keys($usedEnvs ?? []);
        if (1 === \count($envs) && '%env(' . $envs[0] . ')%' === $resolved) {
            return '%env(weasyprint_binary:' . $envs[0] . ')%';
        }

        return BinaryEnvVarProcessor::resolve($binary);
    }
}
