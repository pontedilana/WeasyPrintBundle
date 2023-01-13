<?php

namespace Pontedilana\WeasyprintBundle\WeasyPrint\Response;

/**
 * @deprecated 2.0.0 Image generation is no longer supported by WeasyPrint
 */
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
