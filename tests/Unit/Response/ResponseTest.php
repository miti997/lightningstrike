<?php

namespace Lightningstrike\Tests\Unit\Response;

use PHPUnit\Framework\TestCase;
use Lightningstrike\Response\Response;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Response::class)]
class ResponseTest extends TestCase
{
    protected function setUp(): void
    {
        if (headers_sent()) {
            $this->markTestSkipped('Headers already sent');
        }

        header_remove();
    }

    public function testSendOutputsBodyAndHeaders(): void
    {

        $response = new Response(
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain'],
            'Hello World',
        );

        \ob_start();
        $response->send();
        $output = \ob_get_clean();

        $this->assertSame('Hello World', $output);
        $this->assertSame(['Content-type: text/plain;charset=UTF-8'], xdebug_get_headers());
        $this->assertSame(Response::HTTP_OK, http_response_code());
    }
}
