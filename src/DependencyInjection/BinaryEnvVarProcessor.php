<?php

namespace Pontedilana\WeasyprintBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\Process\ExecutableFinder;

/**
 * Resolves a "%env(weasyprint_binary:NAME)%" value against the PATH at runtime.
 *
 * WeasyprintExtension rewrites a pdf.binary made of a single env var into this
 * form, because the env var value is not known when the container is compiled.
 */
class BinaryEnvVarProcessor implements EnvVarProcessorInterface
{
    public function getEnv(string $prefix, string $name, \Closure $getEnv): string
    {
        $binary = $getEnv($name);
        if (!\is_string($binary)) {
            throw new RuntimeException(\sprintf('The env var "%s" used as the WeasyPrint binary must be a string.', $name));
        }

        return self::resolve($binary);
    }

    public static function getProvidedTypes(): array
    {
        return ['weasyprint_binary' => 'string'];
    }

    /**
     * php-weasyprint verifies the binary with is_executable() before running it,
     * which fails for a bare command name (e.g. "weasyprint") because it is not
     * resolved against the PATH. Resolve a bare command name here so the convenient
     * default keeps working. An explicit path (containing a directory separator) is
     * always returned untouched so a configured path is never silently replaced.
     */
    public static function resolve(string $binary): string
    {
        if (str_contains($binary, \DIRECTORY_SEPARATOR)) {
            return $binary;
        }

        return (new ExecutableFinder())->find($binary) ?? $binary;
    }
}
