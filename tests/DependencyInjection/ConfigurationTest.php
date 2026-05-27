<?php

namespace Pontedilana\WeasyprintBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Pontedilana\WeasyprintBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    private Processor $processor;
    private Configuration $configuration;

    protected function setUp(): void
    {
        $this->processor = new Processor();
        $this->configuration = new Configuration();
    }

    /**
     * @param non-empty-array<int, array> $configs
     * @param non-empty-array<int, array> $expectedConfig
     */
    #[DataProvider('dataForProcessedConfiguration')]
    public function testProcessedConfiguration(array $configs, array $expectedConfig): void
    {
        $config = $this->processor->processConfiguration($this->configuration, $configs);

        self::assertSame($expectedConfig, $config);
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
                        'allowed_schemes' => [],
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
                        'binary' => '/path/to/weasyprint',
                        'options' => ['bak' => 'bap'],
                        'env' => [],
                        'enabled' => true,
                        'allowed_schemes' => [],
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
                        'allowed_schemes' => [],
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
                        'options' => [
                            'foo-bar' => 'baz',
                        ],
                        'env' => [],
                        'enabled' => true,
                        'binary' => 'weasyprint',
                        'allowed_schemes' => [],
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
                        'allowed_schemes' => [],
                    ],
                ],
            ],
        ];
    }

    public function testTemporaryFolderConfiguration(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            [
                'temporary_folder' => '/custom/temp',
            ],
        ]);

        self::assertSame('/custom/temp', $config['temporary_folder']);
    }

    public function testOptionsUnderscoreToHyphenConversion(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            [
                'pdf' => [
                    'options' => [
                        'optimize_images' => 'true',
                        'pdf_version' => '1.7',
                    ],
                ],
            ],
        ]);

        // Underscores should be converted to hyphens
        self::assertArrayHasKey('optimize-images', $config['pdf']['options']);
        self::assertArrayHasKey('pdf-version', $config['pdf']['options']);
        self::assertSame('true', $config['pdf']['options']['optimize-images']);
        self::assertSame('1.7', $config['pdf']['options']['pdf-version']);
    }

    public function testAllowedSchemesDefaultsToEmptyArray(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, []);

        self::assertSame([], $config['pdf']['allowed_schemes']);
    }

    public function testAllowedSchemesConfiguration(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            [
                'pdf' => [
                    'allowed_schemes' => ['http', 'https', 'file'],
                ],
            ],
        ]);

        self::assertSame(['http', 'https', 'file'], $config['pdf']['allowed_schemes']);
    }

    public function testEnvConfiguration(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            [
                'pdf' => [
                    'env' => [
                        'LANG' => 'en_US.UTF-8',
                        'CUSTOM_VAR' => 'value',
                    ],
                ],
            ],
        ]);

        self::assertSame([
            'LANG' => 'en_US.UTF-8',
            'CUSTOM_VAR' => 'value',
        ], $config['pdf']['env']);
    }

    public function testProcessTimeoutMinimumValue(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/process_timeout.*must be a positive integer or false/');

        $this->processor->processConfiguration($this->configuration, [
            [
                'process_timeout' => 0,
            ],
        ]);
    }

    public function testProcessTimeoutNegativeValue(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/process_timeout.*must be a positive integer or false/');

        $this->processor->processConfiguration($this->configuration, [
            [
                'process_timeout' => -10,
            ],
        ]);
    }

    public function testProcessTimeoutValidValue(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            [
                'process_timeout' => 1,
            ],
        ]);

        self::assertSame(1, $config['process_timeout']);
    }

    public function testProcessTimeoutCanBeDisabled(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            [
                'process_timeout' => false,
            ],
        ]);

        self::assertFalse($config['process_timeout']);
    }

    public function testProcessTimeoutRejectsNonIntegerString(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/process_timeout.*must be a positive integer or false/');

        $this->processor->processConfiguration($this->configuration, [
            [
                'process_timeout' => 'foo',
            ],
        ]);
    }

    public function testMultipleOptionsAreMergedCorrectly(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            [
                'pdf' => [
                    'options' => [
                        'first' => 'value1',
                        'second' => 'value2',
                    ],
                ],
            ],
            [
                'pdf' => [
                    'options' => [
                        'second' => 'overridden',
                        'third' => 'value3',
                    ],
                ],
            ],
        ]);

        // performNoDeepMerging means last config wins completely
        self::assertArrayNotHasKey('first', $config['pdf']['options']);
        self::assertSame('overridden', $config['pdf']['options']['second']);
        self::assertSame('value3', $config['pdf']['options']['third']);
    }

    public function testBinaryCanBeEmptyString(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            [
                'pdf' => [
                    'binary' => '',
                ],
            ],
        ]);

        self::assertSame('', $config['pdf']['binary']);
    }

    public function testCompleteConfiguration(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            [
                'temporary_folder' => '/tmp/weasyprint',
                'process_timeout' => 60,
                'pdf' => [
                    'enabled' => true,
                    'binary' => '/usr/local/bin/weasyprint',
                    'options' => [
                        'optimize_images' => 'true',
                        'pdf-version' => '1.7',
                    ],
                    'env' => [
                        'LANG' => 'en_US.UTF-8',
                    ],
                ],
            ],
        ]);

        self::assertSame('/tmp/weasyprint', $config['temporary_folder']);
        self::assertSame(60, $config['process_timeout']);
        self::assertTrue($config['pdf']['enabled']);
        self::assertSame('/usr/local/bin/weasyprint', $config['pdf']['binary']);
        self::assertArrayHasKey('optimize-images', $config['pdf']['options']);
        self::assertArrayHasKey('pdf-version', $config['pdf']['options']);
        self::assertSame(['LANG' => 'en_US.UTF-8'], $config['pdf']['env']);
    }
}
