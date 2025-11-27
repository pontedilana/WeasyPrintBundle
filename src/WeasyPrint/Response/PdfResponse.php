<?php

namespace Pontedilana\WeasyprintBundle\WeasyPrint\Response;

class PdfResponse extends WeasyPrintResponse
{
    /**
     * @param array<string, list<string|null>> $headers
     */
    public function __construct(
        string $content,
        string $fileName = 'output.pdf',
        string $contentType = 'application/pdf',
        string $contentDisposition = 'attachment',
        int $status = 200,
        array $headers = [],
        string $filenameFallback = ''
    ) {
        parent::__construct($content, $fileName, $contentType, $contentDisposition, $status, $headers, $filenameFallback);
    }
}
