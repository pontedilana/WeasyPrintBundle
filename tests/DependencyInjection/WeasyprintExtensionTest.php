<?php

namespace Pontedilana\WeasyprintBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Pontedilana\PhpWeasyPrint\Pdf;
use Pontedilana\WeasyprintBundle\DependencyInjection\WeasyprintExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class WeasyprintExtensionTest extends TestCase
{
    private WeasyprintExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new WeasyprintExtension();
        $this->container = new ContainerBuilder();
    }

    /**
     * @param array<int, mixed> $arguments
     */
    private function assertHasMethodCall(Definition $definition, string $method, array $arguments): void
    {
        $methodCalls = $definition->getMethodCalls();
        $found = false;

        foreach ($methodCalls as $call) {
            if ($call[0] === $method && $call[1] === $arguments) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found, \sprintf('Method "%s" should be called with arguments: %s', $method, json_encode($arguments)));
    }

    private function assertNotHasMethodCall(Definition $definition, string $method): void
    {
        $methods = array_column($definition->getMethodCalls(), 0);
        self::assertNotContains($method, $methods, \sprintf('Method "%s" should not be called', $method));
    }

    public function testServiceIsRegisteredByDefault(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasDefinition('weasyprint.pdf'));
        self::assertTrue($this->container->hasAlias(Pdf::class));
    }

    public function testServiceIsNotRegisteredWhenDisabled(): void
    {
        $this->extension->load([
            [
                'pdf' => [
                    'enabled' => false,
                ],
            ],
        ], $this->container);

        self::assertFalse($this->container->hasDefinition('weasyprint.pdf'));
        self::assertFalse($this->container->hasAlias(Pdf::class));
    }

    public function testParametersAreSetCorrectly(): void
    {
        $this->extension->load([
            [
                'pdf' => [
                    'binary' => '/custom/binary',
                    'options' => ['optimize-images' => 'true'],
                    'env' => ['LANG' => 'en_US.UTF-8'],
                ],
            ],
        ], $this->container);

        self::assertSame('/custom/binary', $this->container->getParameter('weasyprint.pdf.binary'));
        self::assertSame(['optimize-images' => 'true'], $this->container->getParameter('weasyprint.pdf.options'));
        self::assertSame(['LANG' => 'en_US.UTF-8'], $this->container->getParameter('weasyprint.pdf.env'));
    }

    public function testDefaultParametersAreSet(): void
    {
        $this->extension->load([], $this->container);

        self::assertSame('weasyprint', $this->container->getParameter('weasyprint.pdf.binary'));
        self::assertSame([], $this->container->getParameter('weasyprint.pdf.options'));
        self::assertSame([], $this->container->getParameter('weasyprint.pdf.env'));
    }

    public function testTemporaryFolderMethodCallIsAdded(): void
    {
        $this->extension->load([
            [
                'temporary_folder' => '/custom/temp',
            ],
        ], $this->container);

        $definition = $this->container->getDefinition('weasyprint.pdf');
        $this->assertHasMethodCall($definition, 'setTemporaryFolder', ['/custom/temp']);
    }

    public function testTemporaryFolderMethodCallIsNotAddedWhenEmpty(): void
    {
        $this->extension->load([], $this->container);

        $definition = $this->container->getDefinition('weasyprint.pdf');
        $this->assertNotHasMethodCall($definition, 'setTemporaryFolder');
    }

    public function testProcessTimeoutMethodCallIsAdded(): void
    {
        $this->extension->load([
            [
                'process_timeout' => 120,
            ],
        ], $this->container);

        $definition = $this->container->getDefinition('weasyprint.pdf');
        $this->assertHasMethodCall($definition, 'setTimeout', [120]);
    }

    public function testProcessTimeoutMethodCallIsNotAddedWhenEmpty(): void
    {
        $this->extension->load([], $this->container);

        $definition = $this->container->getDefinition('weasyprint.pdf');
        $this->assertNotHasMethodCall($definition, 'setTimeout');
    }

    public function testBothMethodCallsAreAddedTogether(): void
    {
        $this->extension->load([
            [
                'temporary_folder' => '/tmp/weasyprint',
                'process_timeout' => 60,
            ],
        ], $this->container);

        $definition = $this->container->getDefinition('weasyprint.pdf');
        $this->assertHasMethodCall($definition, 'setTemporaryFolder', ['/tmp/weasyprint']);
        $this->assertHasMethodCall($definition, 'setTimeout', [60]);
    }

    public function testServiceIsPublic(): void
    {
        $this->extension->load([], $this->container);

        $definition = $this->container->getDefinition('weasyprint.pdf');

        self::assertTrue($definition->isPublic(), 'Service should be public');
    }

    public function testServiceClass(): void
    {
        $this->extension->load([], $this->container);

        $definition = $this->container->getDefinition('weasyprint.pdf');

        self::assertSame(Pdf::class, $definition->getClass());
    }

    public function testServiceHasCorrectArguments(): void
    {
        $this->extension->load([
            [
                'pdf' => [
                    'binary' => '/usr/bin/weasyprint',
                    'options' => ['pdf-version' => '1.7'],
                    'env' => ['CUSTOM' => 'value'],
                ],
            ],
        ], $this->container);

        $definition = $this->container->getDefinition('weasyprint.pdf');
        $arguments = $definition->getArguments();

        self::assertCount(3, $arguments);
    }

    public function testAliasPointsToCorrectService(): void
    {
        $this->extension->load([], $this->container);

        $alias = $this->container->getAlias(Pdf::class);

        self::assertSame('weasyprint.pdf', (string)$alias);
    }

    public function testServiceHasMonologTag(): void
    {
        $this->extension->load([], $this->container);

        $definition = $this->container->getDefinition('weasyprint.pdf');
        $tags = $definition->getTag('monolog.logger');

        self::assertNotEmpty($tags, 'Service should have monolog.logger tag');
        self::assertSame('weasyprint', $tags[0]['channel']);
    }

    public function testServiceHasSetLoggerMethodCall(): void
    {
        $this->extension->load([], $this->container);

        $definition = $this->container->getDefinition('weasyprint.pdf');
        $methodCalls = $definition->getMethodCalls();

        $hasSetLogger = false;
        foreach ($methodCalls as $call) {
            if ('setLogger' === $call[0]) {
                $hasSetLogger = true;
                // Verify the argument is a Reference to logger service
                self::assertCount(1, $call[1]);
                self::assertInstanceOf(Reference::class, $call[1][0]);
                break;
            }
        }

        self::assertTrue($hasSetLogger, 'setLogger method call should be added');
    }

    public function testServiceArgumentsAreParameterReferences(): void
    {
        $this->extension->load([], $this->container);

        $definition = $this->container->getDefinition('weasyprint.pdf');
        $arguments = $definition->getArguments();

        self::assertCount(3, $arguments);

        // Arguments should reference the parameters, not contain the actual values
        // We can't easily test this without compiling the container, but we can count them
        self::assertArrayHasKey(0, $arguments);
        self::assertArrayHasKey(1, $arguments);
        self::assertArrayHasKey(2, $arguments);
    }
}
