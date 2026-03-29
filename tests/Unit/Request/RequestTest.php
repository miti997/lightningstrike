<?php

namespace Lightningstrike\Tests\Unit\Request;

use Lightningstrike\Request\Request;
use Lightningstrike\Service\HeadersProviderInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(Request::class)]
class RequestTest extends TestCase
{
    private Request $request;

    private HeadersProviderInterface&MockObject $headersProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->headersProvider = $this->createMock(HeadersProviderInterface::class);

        $this->request = new Request($this->headersProvider);
    }

    /** @param array<string,string> $expected */
    #[DataProvider('requestAllDataProvider')]
    public function testGetQueryParams(array $expected): void
    {
        $_GET = $expected;

        $this->headersProvider->expects($this->never())->method('getHeaders');

        $this->assertSame($expected, $this->request->getQueryParams());
    }

    /** @return array<string, list<array<string, string>>> */
    public static function requestAllDataProvider(): array
    {
        return [
            'Empty request' => [[]],
            'Filled request' => [['param1' => 'value1']]
        ];
    }

    /** @param array<string,string> $expected */
    #[DataProvider('requestAllDataProvider')]
    public function testGetBody(array $expected): void
    {
        $_POST = $expected;

        $this->headersProvider->expects($this->never())->method('getHeaders');

        $this->assertSame($expected, $this->request->getBody());
    }

    /** @param array<string,mixed> $get */
    #[DataProvider('requestOneDataProvider')]
    public function testGetQueryParam(array $get, string $path, mixed $expected): void
    {
        $_GET = $get;

        $this->headersProvider->expects($this->never())->method('getHeaders');

        $this->assertSame($expected, $this->request->getQueryParam($path));
    }

    /** @return array<string,mixed> */
    public static function requestOneDataProvider(): array
    {
        return [
            'Empty request' => [
                [],
                'param1',
                null,
            ],
            'No matching param' => [
                ['param1' => 'value1'],
                'param2',
                null,
            ],
            'Matching param' => [
                ['param1' => 'value1'],
                'param1',
                'value1',
            ],
            'Multi dimensional array - full array' => [
                [
                    'param' => [
                        'value1',
                        'value2'
                    ],
                ],
                'param',
                [
                    'value1',
                    'value2'
                ],
            ],
            'Multi dimensional array - one value - numeric key' => [
                [
                    'param' => [
                        'value1',
                        'value2'
                    ],
                ],
                'param.0',
                'value1',
            ],
            'Multi dimensional array - one value - alpha key' => [
                [
                    'param' => [
                        'subkey' => 'value1',
                    ],
                ],
                'param.subkey',
                'value1',
            ],
        ];
    }

    /** @param array<string,mixed> $post */
    #[DataProvider('requestOneDataProvider')]
    public function testGetBodyParam(array $post, string $path, mixed $expected): void
    {
        $_POST = $post;

        $this->headersProvider->expects($this->never())->method('getHeaders');

        $this->assertSame($expected, $this->request->getBodyParam($path));
    }

    public function testIsGet(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->headersProvider->expects($this->never())->method('getHeaders');

        $this->assertTrue($this->request->isGet());
    }

    public function testIsPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->headersProvider->expects($this->never())->method('getHeaders');

        $this->assertTrue($this->request->isPost());
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

        $expected =  [
            $requestForm1 => $header1Value,
            $requestForm2 => $header2Value,
        ];

        $this->headersProvider->expects($this->once())
            ->method('getHeaders')
            ->willReturn($expected);

        $this->assertSame(
            $expected,
            $this->request->getHeaders()
        );
    }

    /**
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $processedHeaders
     */
    #[DataProvider('getHeaderProvider')]
    public function testGetHeaderProperInput(
        array $headers,
        array $processedHeaders,
        string $searchByHeaderName,
        ?string $expectedHeaderValue
    ): void {
        $_SERVER = $headers;

        $this->headersProvider->expects($this->once())
            ->method('getHeaders')
            ->willReturn($processedHeaders);

        $this->assertSame($expectedHeaderValue, $this->request->getHeader($searchByHeaderName));
    }

    /** @return array<string,mixed> */
    public static function getHeaderProvider(): array
    {
        $contentTypeProcessedName = 'Content-Type';
        $acceptLanguageProcessedName = 'Accept-Language';
        $contentTypeValue = 'text/html';
        $acceptLanguageValue = 'ro-RO';
        $headers = [
            'CONTENT_TYPE' => $contentTypeValue,
            'HTTP_ACCEPT_LANGUAGE' => $acceptLanguageValue,
        ];

        $processedHeaders = [
            $contentTypeProcessedName => $contentTypeValue,
            $acceptLanguageProcessedName => $acceptLanguageValue,
        ];
        return [
            "Proper header name - no http" => [
                $headers,
                $processedHeaders,
                $contentTypeProcessedName,
                $contentTypeValue,
            ],
            "Proper header name - http" => [
                $headers,
                $processedHeaders,
                $acceptLanguageProcessedName,
                $acceptLanguageValue,
            ],
            "Mixed case header name = no http" => [
                $headers,
                $processedHeaders,
                'contEnt-tYpe',
                $contentTypeValue,
            ],
            "Mixed case header name = http" => [
                $headers,
                $processedHeaders,
                'ACCEPT-language',
                $acceptLanguageValue,
            ],
            'Non existent header' => [
                $headers,
                $processedHeaders,
                'no-header',
                null,
            ],
        ];
    }

    /** @param array<string,mixed> $cookiesArray */
    #[DataProvider('getAllCookiesDataProvider')]
    public function testGetAllCookies(array $cookiesArray): void
    {
        $_COOKIE = $cookiesArray;

        $this->headersProvider->expects($this->never())
            ->method('getHeaders');


        $this->assertSame($cookiesArray, $this->request->getCookies());
    }

    /** @return array<string,mixed> */
    public static function getAllCookiesDataProvider(): array
    {
        return [
            "No cookies" => [
                [],
            ],
            "Cookies" => [
                [
                    'user_id' => '123',
                    'theme' => 'dark',
                ],
            ],
        ];
    }

    /** @param array<string,mixed> $cookies */
    #[DataProvider('getCookieDataProvider')]
    public function testGetCookie(array $cookies, string $cookieName, mixed $expectedValue): void
    {
        $_COOKIE = $cookies;

        $this->headersProvider->expects($this->never())
            ->method('getHeaders');

        $this->assertSame($expectedValue, $this->request->getCookie($cookieName));
    }

    /** @return array<string,mixed> */
    public static function getCookieDataProvider(): array
    {
        return [
            'Non existent cookie - empty cookies array' => [
                [],
                'nocookie',
                null,
            ],
            'Non existent cookie - no matching value' => [
                ['cookie1' => 'value1'],
                'cookie2',
                null,
            ],
            'Existing cookie - strign value' => [
                ['cookie1' => 'value1'],
                'cookie1',
                'value1',
            ],
            'Existing cookie - array value' => [
                ['cookie1' => ['value1']],
                'cookie1',
                ['value1'],
            ],
            'Existing cookie - array value - with path' => [
                [
                    'cookie1' => [
                        'value1' => 'subValue1',
                    ],
                ],
                'cookie1.value1',
                'subValue1',
            ],
        ];
    }

    public function testHasCookie(): void
    {
        $_COOKIE = ['success' => 'success'];

        $this->headersProvider->expects($this->never())
            ->method('getHeaders');

        $this->assertTrue($this->request->hasCookie('success'));
        $this->assertFalse($this->request->hasCookie('failure'));
    }
}
