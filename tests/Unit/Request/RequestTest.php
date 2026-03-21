<?php

namespace Lightningstrike\Tests\Unit\Request;

use Lightningstrike\Request\Request;
use Lightningstrike\Service\HeadersProviderInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Request::class)]
class RequestTest extends TestCase
{
    /**
     * @param array<string,mixed> $return
     */
    private function addMockHeadersProvider(int $callCount, array $return = []): HeadersProviderInterface
    {
        $provider = $this->createMock(HeadersProviderInterface::class);
        $provider->expects($this->exactly($callCount))
            ->method('getHeaders')
            ->willReturn($return);

        return $provider;
    }

    public function testGetQueryParamsEmptyGet(): void
    {
        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertEquals($_GET, $request->getQueryParams());
    }

    public function testGetQueryParamsGet(): void
    {
        $_GET = ['test' => 'test'];
        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertEquals($_GET, $request->getQueryParams());
    }

    public function testGetQueryParamEmptyGet(): void
    {
        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertEquals(null, $request->getQueryParam('something'));
    }

    public function testGetQueryParamGet(): void
    {
        $paramKey = $paramValue = 'test';
        $_GET = [$paramKey => $paramValue];
        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertEquals($paramValue, $request->getQueryParam($paramKey));
    }

    public function testGetQueryParamWithPath(): void
    {
        $paramKey = 'test';
        $paramValue = [
            'test1' => 'test1',
            'test2'
        ];

        $_GET = [$paramKey => $paramValue];
        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertEquals('test1', $request->getQueryParam($paramKey . '.test1'));
        $this->assertEquals('test2', $request->getQueryParam($paramKey . '.0'));
    }

    public function testGetBodyEmptyPost(): void
    {
        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertEquals($_POST, $request->getBody());
    }

    public function testGetBodyPost(): void
    {
        $_POST = ['test' => 'test'];
        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertEquals($_POST, $request->getBody());
    }

    public function testGetBodyParamEmptyPost(): void
    {
        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertEquals(null, $request->getBodyParam('something'));
    }

    public function testGetBodyParamParamPost(): void
    {
        $paramKey = $paramValue = 'test';
        $_POST = [$paramKey => $paramValue];
        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertEquals($paramValue, $request->getBodyParam($paramKey));
    }

    public function testGetBodyParamWithPath(): void
    {
        $paramKey = 'test';
        $paramValue = [
            'test1' => 'test1',
            'test2'
        ];

        $_POST = [$paramKey => $paramValue];
        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertEquals('test1', $request->getBodyParam($paramKey . '.test1'));
        $this->assertEquals('test2', $request->getBodyParam($paramKey . '.0'));
    }

    public function testIsGet(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertTrue($request->isGet());
    }

    public function testIsPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertTrue($request->isPost());
    }

    public function testGetAllHeaders(): void
    {
        $header1 = 'CONTENT_TYPE';
        $header1Value = 'text/html';
        $header2 = 'HTTP_ACCEPT_LANGUAGE';
        $header2Value = 'ro-RO';

        $requestForm1 = 'Content-Type';
        $requestForm2 = 'Accept-Language';

        $_SERVER[$header1] = $header1Value;
        $_SERVER[$header2] = $header2Value;

        $request = new Request(
            $this->addMockHeadersProvider(
                callCount: 1,
                return:  [
                    $requestForm1 => $header1Value,
                    $requestForm2 => $header2Value,
                ],
            )
        );

        $this->assertSame(
            [
                $requestForm1 => $header1Value,
                $requestForm2 => $header2Value,
            ],
            $request->getHeaders()
        );
    }

    public function testGetHeaderProperInput(): void
    {
        $header1 = 'CONTENT_TYPE';
        $header1Value = 'text/html';
        $header2 = 'HTTP_ACCEPT_LANGUAGE';
        $header2Value = 'ro-RO';

        $requestForm1 = 'Content-Type';
        $requestForm2 = 'Accept-Language';

        $_SERVER[$header1] = $header1Value;
        $_SERVER[$header2] = $header2Value;

        $request = new Request(
            $this->addMockHeadersProvider(
                callCount: 1,
                return:  [
                    $requestForm1 => $header1Value,
                    $requestForm2 => $header2Value,
                ],
            )
        );

        $this->assertSame($header1Value, $request->getHeader($requestForm1));
        $this->assertSame($header2Value, $request->getHeader($requestForm2));
    }

    public function testGetHeaderMixedCaseInput(): void
    {
        $header1 = 'CONTENT_TYPE';
        $header1Value = 'text/html';
        $header2 = 'HTTP_ACCEPT_LANGUAGE';
        $header2Value = 'ro-RO';

        $_SERVER[$header1] = $header1Value;
        $_SERVER[$header2] = $header2Value;

        $input1 = 'contEnt-tYpe';
        $input2 = 'ACCEPT-language';

        $requestForm1 = 'Content-Type';
        $requestForm2 = 'Accept-Language';

        $request = new Request(
            $this->addMockHeadersProvider(
                callCount: 1,
                return:  [
                    $requestForm1 => $header1Value,
                    $requestForm2 => $header2Value,
                ],
            )
        );

        $this->assertSame($header1Value, $request->getHeader($input1));
        $this->assertSame($header2Value, $request->getHeader($input2));
    }

    public function testGetAllCookiesEmpty(): void
    {
        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertSame([], $request->getCookies());
    }

    public function testGetAllCookiesNonEmpty(): void
    {
        $expected = [
            'user_id' => '123',
            'theme' => 'dark',
        ];

        $_COOKIE = $expected;

        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertSame($expected, $request->getCookies());
    }

    public function testGetCookieEmptyCoockieArray(): void
    {
        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertSame(null, $request->getCookie('test'));
    }

    public function testGetCookieNonExistentValue(): void
    {
        $_COOKIE = [
            'succes' => 'success'
        ];

        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertSame(null, $request->getCookie('failure'));
    }

    public function testGetCookieExistentValue(): void
    {
        $_COOKIE = [
            'success' => 'success'
        ];

        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertSame('success', $request->getCookie('success'));
    }

    public function testGetCookieExistentValueArray(): void
    {
        $expected = ['success1', 'success2'];
        $_COOKIE = ['success' => $expected];

        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertSame($expected, $request->getCookie('success'));
    }

    public function testGetCookieExistentValueArrayWithPath(): void
    {
        $_COOKIE = ['success' => ['success1', 'success2']];

        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertSame('success1', $request->getCookie('success.0'));
    }

    public function testHasCookie(): void
    {
        $_COOKIE = ['success' => 'success'];

        $request = new Request($this->addMockHeadersProvider(callCount: 0));

        $this->assertTrue($request->hasCookie('success'));
        $this->assertFalse($request->hasCookie('failure'));
    }
}
