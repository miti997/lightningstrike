<?php

namespace Lightningstrike\Tests\Integration\Middleware;

use Lightningstrike\Middleware\AbstractMiddleware;
use Lightningstrike\Middleware\RoutingMiddleware;
use Lightningstrike\Request\Request;
use Lightningstrike\Request\RequestInterface;
use Lightningstrike\Response\Response;
use Lightningstrike\Response\ResponseInterface;
use Lightningstrike\Routing\RouteBuilder;
use Lightningstrike\Script\AbstractScript;
use Lightningstrike\Tests\Mocks\TestView;
use Lightningstrike\Tests\Mocks\TestViewNotFound;
use Lightningstrike\View\AbstractView;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(RoutingMiddleware::class)]
class RoutingMiddlewareTest extends TestCase
{
    private RoutingMiddleware $routingMiddleware;

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
                            RouteBuilder::PATTERN => '\d+',
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
                            RouteBuilder::HANDLER => AbstractScript::class,
                            RouteBuilder::PATTERN => '\d+',
                            RouteBuilder::MIDDLEWARE => [AbstractMiddleware::class],
                        ],
                    ],
                    RouteBuilder::HANDLER => AbstractScript::class,
                    RouteBuilder::MIDDLEWARE => [
                        AbstractMiddleware::class
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
    ): void
    {
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
        ];
    }
}