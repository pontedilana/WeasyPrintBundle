<?php

namespace Pontedilana\WeasyprintBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class WeasyprintExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        if ($config['pdf']['enabled']) {
            $loader->load('pdf.xml');

            $container->setParameter('weasyprint.pdf.binary', $config['pdf']['binary']);
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

        if ($config['image']['enabled']) {
            $loader->load('image.xml');

            $container->setParameter('weasyprint.image.binary', $config['image']['binary']);
            $container->setParameter('weasyprint.image.options', $config['image']['options']);
            $container->setParameter('weasyprint.image.env', $config['image']['env']);

            if (!empty($config['temporary_folder'])) {
                $container->findDefinition('weasyprint.image')
                    ->addMethodCall('setTemporaryFolder', [$config['temporary_folder']]);
            }
            if (!empty($config['process_timeout'])) {
                $container->findDefinition('weasyprint.image')
                    ->addMethodCall('setTimeout', [$config['process_timeout']]);
            }
        }
    }
}
