<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Pontedilana\PhpWeasyPrint\Pdf;
use Pontedilana\WeasyprintBundle\DependencyInjection\BinaryEnvVarProcessor;

return static function(ContainerConfigurator $container): void {
    $container->services()
        ->set('weasyprint.binary_env_var_processor', BinaryEnvVarProcessor::class)
            ->tag('container.env_var_processor')
        ->set('weasyprint.pdf', Pdf::class)
            ->public()
            ->args([
                param('weasyprint.pdf.binary'),
                param('weasyprint.pdf.options'),
                param('weasyprint.pdf.env'),
                param('weasyprint.pdf.allowed_schemes'),
            ])
            ->call('setLogger', [service('logger')->ignoreOnInvalid()])
            ->tag('monolog.logger', ['channel' => 'weasyprint'])
        ->alias(Pdf::class, 'weasyprint.pdf')
    ;
};
