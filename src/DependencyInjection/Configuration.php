<?php

namespace Pontedilana\WeasyprintBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $fixOptionKeys = static function($options): array {
            $fixedOptions = [];
            foreach ($options as $key => $value) {
                $fixedOptions[(string)str_replace('_', '-', $key)] = $value;
            }

            return $fixedOptions;
        };

        $treeBuilder = new TreeBuilder('weasy_print');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('temporary_folder')->end()
                ->variableNode('process_timeout')
                    ->info('Generator process timeout in seconds; set to false to disable the timeout.')
                    ->validate()
                        ->ifTrue(static fn($value): bool => null !== $value && false !== $value && (!\is_int($value) || $value < 1))
                        ->thenInvalid('The "process_timeout" must be a positive integer or false, got %s.')
                    ->end()
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
                        ->arrayNode('allowed_schemes')
                            ->info('URL schemes allowed for options that accept URLs (e.g. http, https, ftp, file). If not set, php-weasyprint defaults to [http, https].')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
