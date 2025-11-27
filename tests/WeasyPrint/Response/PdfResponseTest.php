<?php

namespace Pontedilana\WeasyprintBundle\Tests\WeasyPrint\Response;

use PHPUnit\Framework\TestCase;
use Pontedilana\WeasyprintBundle\WeasyPrint\Response\PdfResponse;
use Pontedilana\WeasyprintBundle\WeasyPrint\Response\WeasyPrintResponse;

class PdfResponseTest extends TestCase
{
    public function testDefaultParameters(): void
    {
        $response = new PdfResponse('some_binary_output');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('some_binary_output', $response->getContent());
        self::assertSame('application/pdf', $response->headers->get('Content-Type'));

        $disposition = $response->headers->get('Content-Disposition');
        self::assertNotNull($disposition);
        self::assertStringStartsWith('attachment', $disposition);
        self::assertStringContainsString('output.pdf', $disposition);
    }

    public function testCustomFileName(): void
    {
        $response = new PdfResponse('content', 'invoice-2024.pdf');

        $disposition = $response->headers->get('Content-Disposition');
        self::assertNotNull($disposition);
        self::assertStringContainsString('invoice-2024.pdf', $disposition);
    }

    public function testCustomContentType(): void
    {
        $response = new PdfResponse('content', 'test.pdf', 'application/octet-stream');

        self::assertSame('application/octet-stream', $response->headers->get('Content-Type'));
    }

    public function testInlineDisposition(): void
    {
        $response = new PdfResponse('content', 'preview.pdf', 'application/pdf', 'inline');

        $disposition = $response->headers->get('Content-Disposition');
        self::assertNotNull($disposition);
        self::assertStringStartsWith('inline', $disposition);
        self::assertStringContainsString('preview.pdf', $disposition);
    }

    public function testAttachmentDisposition(): void
    {
        $response = new PdfResponse('content', 'download.pdf', 'application/pdf', 'attachment');

        $disposition = $response->headers->get('Content-Disposition');
        self::assertNotNull($disposition);
        self::assertStringStartsWith('attachment', $disposition);
    }

    public function testCustomStatusCode(): void
    {
        $response = new PdfResponse('content', 'test.pdf', 'application/pdf', 'attachment', 201);

        self::assertSame(201, $response->getStatusCode());
    }

    public function testCustomHeaders(): void
    {
        $response = new PdfResponse(
            'content',
            'test.pdf',
            'application/pdf',
            'attachment',
            200,
            ['X-Custom-Header' => ['custom-value']]
        );

        self::assertSame('custom-value', $response->headers->get('X-Custom-Header'));
        self::assertSame('application/pdf', $response->headers->get('Content-Type'));
        // Note: Symfony may add 'private' to Cache-Control automatically
    }

    public function testBinaryContent(): void
    {
        $binaryContent = "%PDF-1.4\n%âãÏÓ\n";
        $response = new PdfResponse($binaryContent, 'document.pdf');

        self::assertSame($binaryContent, $response->getContent());
    }

    public function testFileNameWithSpecialCharactersAutoGeneratesFallback(): void
    {
        $response = new PdfResponse('content', 'fattura_2024_àèìòù.pdf');

        $disposition = $response->headers->get('Content-Disposition');
        self::assertNotNull($disposition);

        // AsciiSlugger transliterates and converts underscores to hyphens
        self::assertStringContainsString('filename=fattura-2024-aeiou.pdf', $disposition);

        // Should contain UTF-8 encoded original filename
        self::assertStringContainsString("filename*=utf-8''fattura_2024_%C3%A0%C3%A8%C3%AC%C3%B2%C3%B9.pdf", $disposition);
    }

    public function testFileNameWithSpecialCharactersAndExplicitFallback(): void
    {
        $response = new PdfResponse(
            'content',
            'prénom_nom_élève.pdf',
            'application/pdf',
            'attachment',
            200,
            [],
            'prenom_nom_eleve.pdf'
        );

        $disposition = $response->headers->get('Content-Disposition');
        self::assertNotNull($disposition);

        // Should use explicit fallback (no quotes needed)
        self::assertStringContainsString('filename=prenom_nom_eleve.pdf', $disposition);

        // Should contain UTF-8 encoded original
        self::assertStringContainsString('filename*=', $disposition);
        self::assertStringContainsString('pr%C3%A9nom', $disposition);
    }

    public function testMultipleInstancesAreIndependent(): void
    {
        $response1 = new PdfResponse('content1', 'file1.pdf');
        $response2 = new PdfResponse('content2', 'file2.pdf');

        self::assertSame('content1', $response1->getContent());
        self::assertSame('content2', $response2->getContent());

        $disposition1 = $response1->headers->get('Content-Disposition');
        $disposition2 = $response2->headers->get('Content-Disposition');

        self::assertStringContainsString('file1.pdf', (string) $disposition1);
        self::assertStringContainsString('file2.pdf', (string) $disposition2);
    }

    public function testAllParametersAtOnce(): void
    {
        $response = new PdfResponse(
            'pdf content here',
            'report.pdf',
            'application/pdf',
            'inline',
            206,
            ['X-Custom' => ['value']]
        );

        self::assertSame('pdf content here', $response->getContent());
        self::assertSame(206, $response->getStatusCode());
        self::assertSame('application/pdf', $response->headers->get('Content-Type'));
        self::assertSame('value', $response->headers->get('X-Custom'));

        $disposition = $response->headers->get('Content-Disposition');
        self::assertNotNull($disposition);
        self::assertStringStartsWith('inline', $disposition);
        self::assertStringContainsString('report.pdf', $disposition);
    }
}
