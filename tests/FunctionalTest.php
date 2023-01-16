<?php

namespace Pontedilana\WeasyprintBundle\Tests;

use PHPUnit\Framework\TestCase;
use Pontedilana\PhpWeasyPrint\Image;
use Pontedilana\PhpWeasyPrint\Pdf;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
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

        $this->assertFalse($container->has('weasyprint.image'), 'The image service is available.');

        $this->expectException(ServiceNotFoundException::class);
        $image = $container->get('weasyprint.image');
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

        $this->assertTrue($container->has('weasyprint.image'));

        $image = $container->get('weasyprint.image');

        $this->assertInstanceof(Image::class, $image);
        $this->assertEquals('/custom/binary/for/weasyprint', $image->getBinary());
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

    public function testEnableImage(): void
    {
        $this->kernel->setConfigurationFilename(__DIR__ . '/fixtures/config/enable_image.yml');
        $this->kernel->boot();

        $container = $this->kernel->getContainer();

        $this->assertTrue($container->has('weasyprint.pdf'), 'The pdf service is available.');
        $this->assertTrue($container->has('weasyprint.image'), 'The image service is NOT available.');
    }
}
