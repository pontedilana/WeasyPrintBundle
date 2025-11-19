<?php

namespace Pontedilana\WeasyprintBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $fixOptionKeys = function($options): array {
            $fixedOptions = [];
            foreach ($options as $key => $value) {
                $fixedOptions[(string)str_replace('_', '-', $key)] = $value;
            }

            return $fixedOptions;
        };

        $treeBuilder = new TreeBuilder('weasy_print');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('temporary_folder')->end()
                ->integerNode('process_timeout')
                    ->min(1)
                    ->info('Generator process timeout in seconds.')
                ->end()
                ->arrayNode('pdf')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('binary')->defaultValue('weasyprint')->end()
                        ->arrayNode('options')
                            ->performNoDeepMerging()
                            ->useAttributeAsKey('name')
                            ->beforeNormalization()
                                ->always($fixOptionKeys)
                            ->end()
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('env')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
