<?php

namespace Pontedilana\WeasyprintBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Pontedilana\WeasyprintBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    /**
     * @param non-empty-array<int, array> $configs
     * @param non-empty-array<int, array> $expectedConfig
     */
    #[DataProvider('dataForProcessedConfiguration')]
    public function testProcessedConfiguration(array $configs, array $expectedConfig): void
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $this->assertEquals($expectedConfig, $config);
    }

    /**
     * @return non-empty-array<int, array>
     */
    public static function dataForProcessedConfiguration(): array
    {
        return [
            [
                [],
                [
                    'pdf' => [
                        'enabled' => true,
                        'binary' => 'weasyprint',
                        'options' => [],
                        'env' => [],
                    ],
                ],
            ],
            [
                [
                    [
                        'pdf' => [
                            'binary' => '/path/to/weasyprint',
                            'options' => ['foo' => 'bar'],
                            'env' => [],
                        ],
                    ],
                    [
                        'pdf' => [
                            'options' => ['bak' => 'bap'],
                            'env' => [],
                        ],
                    ],
                ],
                [
                    'pdf' => [
                        'enabled' => true,
                        'binary' => '/path/to/weasyprint',
                        'options' => ['bak' => 'bap'],
                        'env' => [],
                    ],
                ],
            ],
            [
                [
                    ['pdf' => ['enabled' => false]],
                ],
                [
                    'pdf' => [
                        'enabled' => false,
                        'binary' => 'weasyprint',
                        'options' => [],
                        'env' => [],
                    ],
                ],
            ],
            [
                [
                    [
                        'pdf' => [
                            'options' => [
                                'foo-bar' => 'baz',
                            ],
                            'env' => [],
                        ],
                    ],
                ],
                [
                    'pdf' => [
                        'enabled' => true,
                        'binary' => 'weasyprint',
                        'options' => [
                            'foo-bar' => 'baz',
                        ],
                        'env' => [],
                    ],
                ],
            ],
            [
                [
                    [
                        'process_timeout' => 120,
                    ],
                ],
                [
                    'process_timeout' => 120,
                    'pdf' => [
                        'enabled' => true,
                        'binary' => 'weasyprint',
                        'options' => [],
                        'env' => [],
                    ],
                ],
            ],
        ];
    }
}
