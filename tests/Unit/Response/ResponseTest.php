<?php

namespace Lightningstrike\Tests\Unit\Response;

use PHPUnit\Framework\TestCase;
use Lightningstrike\Response\Response;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Response::class)]
class ResponseTest extends TestCase
{
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
        $this->assertSame(['Content-Type' => 'text/plain'], $response->getHeaders());
        $this->assertSame(Response::HTTP_OK, http_response_code());
    }
}
