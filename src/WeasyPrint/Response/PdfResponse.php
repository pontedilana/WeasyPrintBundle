<?php

namespace Pontedilana\WeasyprintBundle\WeasyPrint\Response;

class PdfResponse extends WeasyPrintResponse
{
    public function __construct(
        $content,
        $fileName = 'output.pdf',
        $contentType = 'application/pdf',
        $contentDisposition = 'attachment',
        $status = 200,
        $headers = []
    ) {
        parent::__construct($content, $fileName, $contentType, $contentDisposition, $status, $headers);
    }
}
