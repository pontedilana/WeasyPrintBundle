# WeasyPrintBundle
[PhpWeasyPrint](https://github.com/pontedilana/php-weasyprint) is a PHP (7.4+) wrapper for [WeasyPrint](https://weasyprint.org/) PDF generator.
It allows you to generate PDF files from HTML string or URL.

The WeasyPrintBundle provides a simple integration for your Symfony project.

This bundle is massively inspired by [KnpLabs/KnpSnappyBundle](https://github.com/KnpLabs/KnpSnappyBundle), of which it aims to be a one-to-one substitute

## Installation

With [composer](https://getcomposer.org), require:

`composer require pontedilana/weasyprint-bundle`

Then enable it in your kernel (a flex recipe is coming soon):

```php
// config/bundles.php
<?php

return [
    //...
    Pontedilana\WeasyprintBundle\WeasyprintBundle::class => ['all' => true],
    //...
];
```

## Configuration
If you need to change the binaries, change the instance options or even disable one or both services, you can do it through the configuration.

```yaml
# config/packages/weasyprint.yaml
weasyprint:
    pdf:
        enabled:    true
        binary:     /usr/local/bin/weasyprint
        options:    []
```

If you want to change temporary folder which is ```sys_get_temp_dir()``` by default, you can use

```yaml
# config/packages/weasyprint.yaml
weasyprint:
    temporary_folder: "%kernel.cache_dir%/weasyprint"
```

You can also configure the timeout used by the generators with `process_timeout`:

```yaml
# config/packages/weasyprint.yaml
weasyprint:
    process_timeout: 20 # In seconds
```

## Usage

The bundle registers one service:

 - the `weasyprint.pdf` service allows you to generate pdf files.

### Generate a PDF document from a URL

```php
// @var Pontedilana\PhpWeasyPrint\Pdf
$weasyprintPdf->generate('https://www.github.com', '/path/to/the/file.pdf');
```

### Generate a PDF document from a twig view

```php
// @var Pontedilana\PhpWeasyPrint\Pdf
$weasyprintPdf->generateFromHtml(
    $this->renderView(
        'frontend/product/pdf.html.twig',
        [
            'some'  => $vars,
        ]
    ),
    '/path/to/the/file.pdf'
);
```

### Render a PDF document as a response from a controller

```php
use Pontedilana\WeasyprintBundle\WeasyPrint\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SomeController extends AbstractController
{
    public function pdf(Pontedilana\PhpWeasyPrint\Pdf $weasyprintPdf)
    {
        $html = $this->renderView(
            'frontend/product/pdf.html.twig',
            [
                'some'  => $vars,
            ]
        );

        return new PdfResponse(
            $weasyprintPdf->getOutputFromHtml($html),
            'file.pdf'
        );
    }
}
```

**Note:** Filenames with accented characters (e.g., `invoice_àèìòù.pdf`) are automatically supported. The bundle generates an ASCII-safe fallback for older browsers while preserving the original UTF-8 filename for modern browsers (following [RFC 6266](https://www.rfc-editor.org/rfc/rfc6266.html)).

### Render a PDF document with a relative url inside like CSS files or images

```php
use Pontedilana\WeasyprintBundle\WeasyPrint\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SomeController extends AbstractController
{
    public function pdf(Pontedilana\PhpWeasyPrint\Pdf $weasyprintPdf)
    {
        $pageUrl = $this->generateUrl('homepage', [], true); // use absolute path!

        return new PdfResponse(
            $weasyprintPdf->getOutput($pageUrl),
            'file.pdf'
        );
    }
}
```

## Credits

WeasyPrintBundle and [PhpWeasyPrint](https://github.com/pontedilana/php-weasyprint) have been developed by [Pontedilana](https://www.pontedilana.it/).  
SnappyBundle has been developed by [KnpLabs](https://knplabs.com).
