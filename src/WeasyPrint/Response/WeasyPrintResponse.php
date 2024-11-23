<?php

namespace Pontedilana\WeasyprintBundle\WeasyPrint\Response;

use Symfony\Component\HttpFoundation\Response as Base;

class WeasyPrintResponse extends Base
{
    /**
     * @param array<string, list<string|null>> $headers
     */
    public function __construct(
        string $content,
        string $fileName,
        ?string $contentType,
        string $contentDisposition = 'attachment',
        int $status = 200,
        array $headers = []
    ) {
        parent::__construct($content, $status, $headers);
        $this->headers->add(['Content-Type' => $contentType]);
        $this->headers->add(['Content-Disposition' => $this->headers->makeDisposition($contentDisposition, $fileName)]);
    }
}
