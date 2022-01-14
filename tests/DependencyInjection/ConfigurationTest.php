<?php

namespace Pontedilana\WeasyprintBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Pontedilana\WeasyprintBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    /**
     * @dataProvider dataForProcessedConfiguration
     *
     * @param non-empty-array<int, array> $configs
     * @param non-empty-array<int, array> $expectedConfig
     */
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
    public function dataForProcessedConfiguration(): array
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
                    'image' => [
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
                        'image' => [
                            'binary' => '/path/to/weasyprint',
                            'options' => ['baz' => 'bat', 'baf' => 'bag'],
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
                    'image' => [
                        'enabled' => true,
                        'binary' => '/path/to/weasyprint',
                        'options' => ['baz' => 'bat', 'baf' => 'bag'],
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
                    'image' => [
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
                            'options' => [
                                'foo-bar' => 'baz',
                            ],
                            'env' => [],
                        ],
                        'image' => [
                            'options' => [
                                'bag-baf' => 'bak',
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
                    'image' => [
                        'enabled' => true,
                        'binary' => 'weasyprint',
                        'options' => [
                            'bag-baf' => 'bak',
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
                    'image' => [
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
