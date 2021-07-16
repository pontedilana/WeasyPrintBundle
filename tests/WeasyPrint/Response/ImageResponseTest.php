<?php

namespace Pontedilana\WeasyprintBundle\Tests\WeasyPrint\Response;

use PHPUnit\Framework\TestCase;
use Pontedilana\WeasyprintBundle\WeasyPrint\Response\ImageResponse;

class ImageResponseTest extends TestCase
{
    public function testDefaultParameters()
    {
        $response = new ImageResponse('some_binary_output');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('some_binary_output', $response->getContent());
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertSame('inline; filename=output.png', str_replace('"', '', $response->headers->get('Content-Disposition')));
    }

    public function testSetDifferentMimeType()
    {
        $response = new ImageResponse('some_binary_output', 'test.png', 'application/octet-stream');

        $this->assertSame('application/octet-stream', $response->headers->get('Content-Type'));
    }

    public function testSetDifferentFileName()
    {
        $fileName = 'test.png';
        $response = new ImageResponse('some_binary_output', $fileName);
        $fileNameFromDispositionRegex = '/.*filename=([^"]+)/';

        $this->assertSame(1, preg_match($fileNameFromDispositionRegex, str_replace('"', '', $response->headers->get('Content-Disposition')), $matches), 1);

        $this->assertSame($fileName, $matches[1]);
    }
}
