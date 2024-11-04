<?php

namespace Pontedilana\WeasyprintBundle\WeasyPrint\Response;

use Symfony\Component\HttpFoundation\Response as Base;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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
        $contentDispositionDirectives = [
            ResponseHeaderBag::DISPOSITION_INLINE,
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        ];
        if (!\in_array($contentDisposition, $contentDispositionDirectives, true)) {
            throw new \InvalidArgumentException(\sprintf('Expected one of the following directives: "%s", but "%s" given.', implode('", "', $contentDispositionDirectives), $contentDisposition));
        }

        parent::__construct($content, $status, $headers);
        $this->headers->add(['Content-Type' => $contentType]);
        $this->headers->add(['Content-Disposition' => $this->headers->makeDisposition($contentDisposition, $fileName)]);
    }
}
