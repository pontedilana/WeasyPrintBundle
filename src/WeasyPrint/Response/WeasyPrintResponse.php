<?php

namespace Pontedilana\WeasyprintBundle\WeasyPrint\Response;

use Symfony\Component\HttpFoundation\Response as Base;
use Symfony\Component\String\Slugger\AsciiSlugger;

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
        array $headers = [],
        string $filenameFallback = ''
    ) {
        parent::__construct($content, $status, $headers);
        $this->headers->add(['Content-Type' => $contentType]);

        if ('' === $filenameFallback) {
            $filenameFallback = $this->generateAsciiFallback($fileName);
        }

        $this->headers->add(['Content-Disposition' => $this->headers->makeDisposition($contentDisposition, $fileName, $filenameFallback)]);
    }

    /**
     * Generate an ASCII-safe fallback filename by transliterating non-ASCII characters.
     */
    private function generateAsciiFallback(string $filename): string
    {
        // If already ASCII, return as-is
        if (preg_match('/^[\x20-\x7e]*$/', $filename)) {
            return $filename;
        }

        // Use Symfony's AsciiSlugger for proper transliteration
        // Preserve the file extension
        $parts = pathinfo($filename);
        $basename = $parts['filename'];
        $extension = isset($parts['extension']) ? '.' . $parts['extension'] : '';

        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($basename)->toString();

        return $slug . $extension;
    }
}
