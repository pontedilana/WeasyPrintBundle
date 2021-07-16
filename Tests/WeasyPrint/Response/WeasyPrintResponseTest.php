<?php

namespace Pontedilana\WeasyprintBundle\Tests\WeasyPrint\Response;

use PHPUnit\Framework\TestCase;
use Pontedilana\WeasyprintBundle\WeasyPrint\Response\WeasyPrintResponse;

class WeasyPrintResponseTest extends TestCase
{
    public function testExceptionMessage()
    {
        try {
            new WeasyPrintResponse('', 'test.jpg', 'image/jpg', 'foo');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('Expected one of the following directives: "inline", "attachment", but "foo" given.', $e->getMessage());
        }
    }
}
