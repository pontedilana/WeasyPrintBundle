<?php

namespace Pontedilana\WeasyprintBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Pontedilana\WeasyprintBundle\DependencyInjection\BinaryEnvVarProcessor;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\Process\ExecutableFinder;

class BinaryEnvVarProcessorTest extends TestCase
{
    private function process(mixed $envValue): string
    {
        return (new BinaryEnvVarProcessor())->getEnv('weasyprint_binary', 'WEASYPRINT_BINARY', static fn(): mixed => $envValue);
    }

    public function testBareNameIsResolvedAgainstThePath(): void
    {
        $expected = (new ExecutableFinder())->find('sh');
        self::assertNotNull($expected);

        self::assertSame($expected, $this->process('sh'));
    }

    public function testExplicitPathIsKept(): void
    {
        self::assertSame('/custom/weasyprint', $this->process('/custom/weasyprint'));
    }

    public function testUnknownBareNameIsKept(): void
    {
        self::assertSame('no-such-weasyprint-binary', $this->process('no-such-weasyprint-binary'));
    }

    public function testNonStringValueIsRejected(): void
    {
        $this->expectException(RuntimeException::class);

        $this->process(42);
    }

    public function testProvidedTypes(): void
    {
        self::assertSame(['weasyprint_binary' => 'string'], BinaryEnvVarProcessor::getProvidedTypes());
    }
}
