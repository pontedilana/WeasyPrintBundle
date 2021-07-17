<?php

namespace Pontedilana\WeasyprintBundle\WeasyPrint\Response;

class ImageResponse extends WeasyPrintResponse
{
    public function __construct($content, $fileName = 'output.png', $contentType = 'image/png', $contentDisposition = 'inline', $status = 200, $headers = [])
    {
        parent::__construct($content, $fileName, $contentType, $contentDisposition, $status, $headers);
    }
}
