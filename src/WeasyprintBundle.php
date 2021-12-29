<?php

namespace Pontedilana\WeasyprintBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle as BaseBundle;

class WeasyprintBundle extends BaseBundle
{
    /**
     * {@inheritdoc}
     */
    public function getNamespace(): string
    {
        return __NAMESPACE__;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return str_replace('\\', '/', __DIR__);
    }
}
