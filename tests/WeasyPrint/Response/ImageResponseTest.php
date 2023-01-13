<?php

namespace Pontedilana\WeasyprintBundle\Tests\WeasyPrint\Response;

use PHPUnit\Framework\TestCase;
use Pontedilana\WeasyprintBundle\WeasyPrint\Response\ImageResponse;

/**
 * @deprecated 2.0.0 Image generation is no longer supported by WeasyPrint
 */
class ImageResponseTest extends TestCase
{
    public function testDefaultParameters(): void
    {
        $response = new ImageResponse('some_binary_output');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('some_binary_output', $response->getContent());
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertSame('inline; filename=output.png', str_replace('"', '', (string)$response->headers->get('Content-Disposition')));
    }

    public function testSetDifferentMimeType(): void
    {
        $response = new ImageResponse('some_binary_output', 'test.png', 'application/octet-stream');

        $this->assertSame('application/octet-stream', $response->headers->get('Content-Type'));
    }

    public function testSetDifferentFileName(): void
    {
        $fileName = 'test.png';
        $response = new ImageResponse('some_binary_output', $fileName);
        $fileNameFromDispositionRegex = '/.*filename=([^"]+)/';

        $this->assertSame(1, preg_match($fileNameFromDispositionRegex, str_replace('"', '', (string)$response->headers->get('Content-Disposition')), $matches), '1');

        $this->assertSame($fileName, $matches[1]);
    }
}
