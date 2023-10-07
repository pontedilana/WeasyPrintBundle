<?php

namespace Pontedilana\WeasyprintBundle\Tests;

use PHPUnit\Framework\TestCase;
use Pontedilana\PhpWeasyPrint\Pdf;
use Symfony\Component\Filesystem\Filesystem;

class FunctionalTest extends TestCase
{
    private TestKernel $kernel;

    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->kernel = new TestKernel(uniqid(), false);

        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->kernel->getCacheDir());
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->kernel->getCacheDir());
    }

    public function testServiceIsAvailableOutOfTheBox(): void
    {
        $this->kernel->setConfigurationFilename(__DIR__ . '/fixtures/config/out_of_the_box.yml');
        $this->kernel->boot();

        $container = $this->kernel->getContainer();

        $this->assertTrue($container->has('weasyprint.pdf'), 'The pdf service is available.');

        $pdf = $container->get('weasyprint.pdf');

        $this->assertInstanceof(Pdf::class, $pdf);
        $this->assertEquals('weasyprint', $pdf->getBinary());
    }

    public function testChangeBinaries(): void
    {
        $this->kernel->setConfigurationFilename(__DIR__ . '/fixtures/config/change_binaries.yml');
        $this->kernel->boot();

        $container = $this->kernel->getContainer();

        $this->assertTrue($container->has('weasyprint.pdf'));

        $pdf = $container->get('weasyprint.pdf');

        $this->assertInstanceof(Pdf::class, $pdf);
        $this->assertEquals('/custom/binary/for/weasyprint', $pdf->getBinary());
    }

    public function testChangeTemporaryFolder(): void
    {
        $this->kernel->setConfigurationFilename(__DIR__ . '/fixtures/config/change_temporary_folder.yml');
        $this->kernel->boot();

        $container = $this->kernel->getContainer();

        $pdf = $container->get('weasyprint.pdf');
        $this->assertInstanceof(Pdf::class, $pdf);
        $this->assertEquals('/path/to/the/tmp', $pdf->getTemporaryFolder());
    }

    public function testDisablePdf(): void
    {
        $this->kernel->setConfigurationFilename(__DIR__ . '/fixtures/config/disable_pdf.yml');
        $this->kernel->boot();

        $container = $this->kernel->getContainer();

        $this->assertFalse($container->has('weasyprint.pdf'), 'The pdf service is NOT available.');
    }
}
