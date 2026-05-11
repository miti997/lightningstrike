<?php

namespace Lightningstrike\Tests\Integration\Middleware;

use Lightningstrike\Exception\Request\InvalidRequestMethod;
use Lightningstrike\Exception\Request\NoRouteMatched;
use Lightningstrike\Middleware\RoutingMiddleware;
use Lightningstrike\Request\Request;
use Lightningstrike\Request\RequestInterface;
use Lightningstrike\Response\Response;
use Lightningstrike\Routing\RouteBuilder;
use Lightningstrike\Tests\Mocks\TestMiddleware;
use Lightningstrike\Tests\Mocks\TestScript;
use Lightningstrike\Tests\Mocks\TestView;
use Lightningstrike\Tests\Mocks\TestViewNotFound;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RoutingMiddleware::class)]
class RoutingMiddlewareTest extends TestCase
{
    private RoutingMiddleware $routingMiddleware;

    /** @var array<string,mixed> $routes */
    private array $routes = [
        Request::METHOD_GET => [
            RouteBuilder::FIXED => [
                'products' => [
                    RouteBuilder::FIXED => [],
                    RouteBuilder::DYNAMIC => [
                        'id' => [
                            RouteBuilder::FIXED => [],
                            RouteBuilder::DYNAMIC => [],
                            RouteBuilder::HANDLER => TestViewNotFound::class,
                            RouteBuilder::PATTERN => null,
                            RouteBuilder::MIDDLEWARE => [],
                        ],
                    ],
                    RouteBuilder::HANDLER => TestView::class,
                    RouteBuilder::MIDDLEWARE => [],
                ],
                'users' => [
                    RouteBuilder::FIXED => [],
                    RouteBuilder::DYNAMIC => [],
                    RouteBuilder::HANDLER => TestView::class,
                    RouteBuilder::MIDDLEWARE => [],
                ],
            ],
            RouteBuilder::DYNAMIC => [
                'id' => [
                    RouteBuilder::FIXED => [
                        'test' => [
                            RouteBuilder::FIXED => [],
                            RouteBuilder::DYNAMIC => [],
                            RouteBuilder::HANDLER => TestView::class,
                            RouteBuilder::MIDDLEWARE => [],
                        ],
                    ],
                    RouteBuilder::DYNAMIC => [
                        'secondId' => [
                            RouteBuilder::FIXED => [],
                            RouteBuilder::DYNAMIC => [],
                            RouteBuilder::HANDLER => TestView::class,
                            RouteBuilder::PATTERN => '/\d+/',
                            RouteBuilder::MIDDLEWARE => [],
                        ],
                    ],
                    RouteBuilder::HANDLER => TestViewNotFound::class,
                    RouteBuilder::PATTERN => null,
                    RouteBuilder::MIDDLEWARE => [],
                ],
            ],
            RouteBuilder::HANDLER => TestView::class,
            RouteBuilder::MIDDLEWARE => [],
        ],
        Request::METHOD_POST => [
            RouteBuilder::FIXED => [
                'users' => [
                    RouteBuilder::FIXED => [],
                    RouteBuilder::DYNAMIC => [
                        'id' => [
                            RouteBuilder::FIXED => [],
                            RouteBuilder::DYNAMIC => [],
                            RouteBuilder::HANDLER => TestScript::class,
                            RouteBuilder::PATTERN => '/\d+/',
                            RouteBuilder::MIDDLEWARE => [TestMiddleware::class],
                        ],
                    ],
                    RouteBuilder::HANDLER => TestScript::class,
                    RouteBuilder::MIDDLEWARE => [
                        TestMiddleware::class
                    ],
                ]
            ],
            RouteBuilder::DYNAMIC => [],
            RouteBuilder::HANDLER => null,
            RouteBuilder::MIDDLEWARE => [],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->routingMiddleware = new RoutingMiddleware($this->routes);
    }

    #[DataProvider('matchRouteDataProvider')]
    public function testMatchRoute(
        string $requestMethod,
        string $uri,
        int $statusCode,
        string $body,
    ): void {
        $request = $this->createMock(RequestInterface::class);

        $request->expects($this->once())
            ->method('getRequestMethod')
            ->willReturn($requestMethod);

        $request->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $actualResponse = $this->routingMiddleware->handle($request);

        $this->assertEquals($statusCode, $actualResponse->getStatusCode());
        $this->assertEquals($body, $actualResponse->getBody());
    }

    /** @return array<string, list<int|string>> */
    public static function matchRouteDataProvider(): array
    {
        $success = 'Success';
        $notFound = 'Not found';
        return [
            'No URI GET' => [
                Request::METHOD_GET,
                '/',
                Response::HTTP_OK,
                $success,
            ],
            'Products GET' => [
                Request::METHOD_GET,
                '/products',
                Response::HTTP_OK,
                $success,
            ],
            'Product GET' => [
                Request::METHOD_GET,
                '/products/1',
                Response::HTTP_NOT_FOUND,
                $notFound,
            ],
            'Users GET' => [
                Request::METHOD_GET,
                '/users',
                Response::HTTP_OK,
                $success,
            ],
            'GET id' => [
                Request::METHOD_GET,
                '/333',
                Response::HTTP_NOT_FOUND,
                $notFound,
            ],
            'GET id test' => [
                Request::METHOD_GET,
                '/333/test',
                Response::HTTP_OK,
                $success,
            ],
            'GET id secondId' => [
                Request::METHOD_GET,
                '/333/333',
                Response::HTTP_OK,
                $success,
            ],
            'POST users - includes middleware' => [
                Request::METHOD_POST,
                'users',
                Response::HTTP_FOUND,
                $success,
            ],
            'POST user - includes middleware' => [
                Request::METHOD_POST,
                'users/3',
                Response::HTTP_FOUND,
                $success,
            ],
        ];
    }

    public function testInvalidRequestMethod(): void
    {
        $request = $this->createMock(RequestInterface::class);

        $request->expects($this->once())
            ->method('getRequestMethod')
            ->willReturn('PUT');

        $request->expects($this->never())
            ->method('getUri');

        $this->expectException(InvalidRequestMethod::class);
        $this->routingMiddleware->handle($request);
    }

    public function testRouteNotInArray(): void
    {
        $request = $this->createMock(RequestInterface::class);

        $request->expects($this->once())
            ->method('getRequestMethod')
            ->willReturn(Request::METHOD_GET);

        $request->expects($this->once())
            ->method('getUri')
            ->willReturn('/some/wrong/uri');

        $this->expectException(NoRouteMatched::class);
        $this->routingMiddleware->handle($request);
    }
}
