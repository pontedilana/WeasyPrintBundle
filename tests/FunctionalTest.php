<?php

namespace Pontedilana\WeasyprintBundle\Tests;

use PHPUnit\Framework\TestCase;
use Pontedilana\PhpWeasyPrint\Pdf;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class FunctionalTest extends TestCase
{
    private function createKernel(string ...$additionalConfigs): TestKernel
    {
        $kernel = new TestKernel('test', false);

        match (Kernel::MAJOR_VERSION) {
            8 => $kernel->addConfigurationFilename(__DIR__ . '/fixtures/config/base_symfony_8.yml'),
            7 => $kernel->addConfigurationFilename(__DIR__ . '/fixtures/config/base_symfony_7.yml'),
            default => $kernel->addConfigurationFilename(__DIR__ . '/fixtures/config/base_symfony_6.yml'),
        };

        foreach ($additionalConfigs as $config) {
            $kernel->addConfigurationFilename($config);
        }

        return $kernel;
    }

    private function cleanupKernel(TestKernel $kernel): void
    {
        $cacheDir = $kernel->getCacheDir();
        $kernel->shutdown();

        $filesystem = new Filesystem();
        $filesystem->remove($cacheDir);
    }

    public function testServiceIsAvailableOutOfTheBox(): void
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        $container = $kernel->getContainer();

        self::assertTrue($container->has('weasyprint.pdf'));

        $pdf = $container->get('weasyprint.pdf');

        self::assertInstanceOf(Pdf::class, $pdf);
        self::assertSame('weasyprint', $pdf->getBinary());

        $this->cleanupKernel($kernel);
    }

    public function testChangeBinaries(): void
    {
        $kernel = $this->createKernel(__DIR__ . '/fixtures/config/change_binaries.yml');
        $kernel->boot();

        $container = $kernel->getContainer();

        self::assertTrue($container->has('weasyprint.pdf'));

        $pdf = $container->get('weasyprint.pdf');

        self::assertInstanceOf(Pdf::class, $pdf);
        self::assertSame('/custom/binary/for/weasyprint', $pdf->getBinary());

        $this->cleanupKernel($kernel);
    }

    public function testChangeTemporaryFolder(): void
    {
        $kernel = $this->createKernel(__DIR__ . '/fixtures/config/change_temporary_folder.yml');
        $kernel->boot();

        $container = $kernel->getContainer();

        $pdf = $container->get('weasyprint.pdf');
        self::assertInstanceOf(Pdf::class, $pdf);
        self::assertSame('/path/to/the/tmp', $pdf->getTemporaryFolder());

        $this->cleanupKernel($kernel);
    }

    public function testDisablePdf(): void
    {
        $kernel = $this->createKernel(__DIR__ . '/fixtures/config/disable_pdf.yml');
        $kernel->boot();

        $container = $kernel->getContainer();

        self::assertFalse($container->has('weasyprint.pdf'));

        $this->cleanupKernel($kernel);
    }
}
