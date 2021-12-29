<?php

namespace Pontedilana\WeasyprintBundle\WeasyPrint\Response;

class ImageResponse extends WeasyPrintResponse
{
    public function __construct(
        string $content,
        string $fileName = 'output.png',
        string $contentType = 'image/png',
        string $contentDisposition = 'inline',
        int $status = 200,
        array $headers = []
    ) {
        parent::__construct($content, $fileName, $contentType, $contentDisposition, $status, $headers);
    }
}
