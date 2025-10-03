<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Pontedilana\PhpWeasyPrint\Pdf;

return static function(ContainerConfigurator $container): void {
    $container->services()
        ->set('weasyprint.pdf', Pdf::class)
            ->public()
            ->args([
                param('weasyprint.pdf.binary'),
                param('weasyprint.pdf.options'),
                param('weasyprint.pdf.env'),
            ])
            ->call('setLogger', [service('logger')->ignoreOnInvalid()])
            ->tag('monolog.logger', ['channel' => 'weasyprint'])
        ->alias(Pdf::class, 'weasyprint.pdf')
    ;
};
