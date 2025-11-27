<?php

namespace Pontedilana\WeasyprintBundle\Tests\WeasyPrint\Response;

use PHPUnit\Framework\TestCase;
use Pontedilana\WeasyprintBundle\WeasyPrint\Response\WeasyPrintResponse;

class WeasyPrintResponseTest extends TestCase
{
    public function testDefaultAttachmentDisposition(): void
    {
        $response = new WeasyPrintResponse('content', 'test.pdf', 'application/pdf');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('content', $response->getContent());
        self::assertSame('application/pdf', $response->headers->get('Content-Type'));

        $disposition = $response->headers->get('Content-Disposition');
        self::assertNotNull($disposition);
        self::assertStringStartsWith('attachment', $disposition);
        self::assertStringContainsString('test.pdf', $disposition);
    }

    public function testInlineDisposition(): void
    {
        $response = new WeasyPrintResponse('content', 'document.pdf', 'application/pdf', 'inline');

        $disposition = $response->headers->get('Content-Disposition');
        self::assertNotNull($disposition);
        self::assertStringStartsWith('inline', $disposition);
        self::assertStringContainsString('document.pdf', $disposition);
    }

    public function testCustomStatusCode(): void
    {
        $response = new WeasyPrintResponse('content', 'file.pdf', 'application/pdf', 'attachment', 201);

        self::assertSame(201, $response->getStatusCode());
    }

    public function testCustomHeaders(): void
    {
        $response = new WeasyPrintResponse(
            'content',
            'file.pdf',
            'application/pdf',
            'attachment',
            200,
            ['X-Custom-Header' => ['custom-value']]
        );

        self::assertSame('custom-value', $response->headers->get('X-Custom-Header'));
    }

    public function testNullContentType(): void
    {
        $response = new WeasyPrintResponse('content', 'file.pdf', null);

        self::assertNull($response->headers->get('Content-Type'));
    }

    public function testFileNameWithSpecialCharactersAutoGeneratesFallback(): void
    {
        $response = new WeasyPrintResponse('content', 'documento àèéì.pdf', 'application/pdf');

        $disposition = $response->headers->get('Content-Disposition');
        self::assertNotNull($disposition);

        // AsciiSlugger transliterates and converts spaces to hyphens
        self::assertStringContainsString('filename=documento-aeei.pdf', $disposition);

        // Should contain UTF-8 encoded version
        self::assertStringContainsString("filename*=utf-8''documento%20%C3%A0%C3%A8%C3%A9%C3%AC.pdf", $disposition);
    }

    public function testFileNameWithSpecialCharactersAndCustomFallback(): void
    {
        $response = new WeasyPrintResponse(
            'content',
            'fattura_2024_àèìòù.pdf',
            'application/pdf',
            'attachment',
            200,
            [],
            'fattura_2024.pdf'
        );

        $disposition = $response->headers->get('Content-Disposition');
        self::assertNotNull($disposition);

        // Should use custom fallback (no quotes needed, no special chars)
        self::assertStringContainsString('filename=fattura_2024.pdf', $disposition);

        // Should contain UTF-8 encoded original
        self::assertStringContainsString('filename*=', $disposition);
    }

    public function testFileNameWithSpaces(): void
    {
        $response = new WeasyPrintResponse('content', 'my document.pdf', 'application/pdf');

        $disposition = $response->headers->get('Content-Disposition');
        self::assertNotNull($disposition);
        self::assertStringContainsString('my', $disposition);
        self::assertStringContainsString('document.pdf', $disposition);
    }

    public function testInvalidDispositionThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The disposition must be either "attachment" or "inline".');

        new WeasyPrintResponse('content', 'test.pdf', 'application/pdf', 'invalid');
    }

    public function testContentIsSetCorrectly(): void
    {
        $binaryContent = "\x00\x01\x02\x03";
        $response = new WeasyPrintResponse($binaryContent, 'binary.pdf', 'application/pdf');

        self::assertSame($binaryContent, $response->getContent());
    }

    public function testEmptyContent(): void
    {
        $response = new WeasyPrintResponse('', 'empty.pdf', 'application/pdf');

        self::assertSame('', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
    }
}
