<?php

namespace Lightningstrike\Tests\Unit\Request;

use Lightningstrike\Request\Request;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Request::class)]
class RequestTest extends TestCase
{
    public function testGetQueryParamsEmptyGet(): void
    {
        $request = new Request();

        $this->assertEquals($_GET, $request->getQueryParams());
    }

    public function testGetQueryParamsGet(): void
    {
        $_GET = ['test' => 'test'];
        $request = new Request();

        $this->assertEquals($_GET, $request->getQueryParams());
    }

    public function testGetQueryParamEmptyGet(): void
    {
        $request = new Request();

        $this->assertEquals(null, $request->getQueryParam('something'));
    }

    public function testGetQueryParamGet(): void
    {
        $paramKey = $paramValue = 'test';
        $_GET = [$paramKey => $paramValue];
        $request = new Request();

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
        $request = new Request();

        $this->assertEquals('test1', $request->getQueryParam($paramKey . '.test1'));
        $this->assertEquals('test2', $request->getQueryParam($paramKey . '.0'));
    }

    public function testGetBodyEmptyPost(): void
    {
        $request = new Request();

        $this->assertEquals($_POST, $request->getBody());
    }

    public function testGetBodyPost(): void
    {
        $_POST = ['test' => 'test'];
        $request = new Request();

        $this->assertEquals($_POST, $request->getBody());
    }

    public function testGetBodyParamEmptyPost(): void
    {
        $request = new Request();

        $this->assertEquals(null, $request->getBodyParam('something'));
    }

    public function testGetBodyParamParamPost(): void
    {
        $paramKey = $paramValue = 'test';
        $_POST = [$paramKey => $paramValue];
        $request = new Request();

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
        $request = new Request();

        $this->assertEquals('test1', $request->getBodyParam($paramKey . '.test1'));
        $this->assertEquals('test2', $request->getBodyParam($paramKey . '.0'));
    }

    public function testIsGet(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $request = new Request();

        $this->assertTrue($request->isGet());
    }

    public function testIsPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new Request();

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

        $request = new Request();

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

        $request = new Request();

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

        $request = new Request();

        $this->assertSame($header1Value, $request->getHeader($input1));
        $this->assertSame($header2Value, $request->getHeader($input2));
    }
}
