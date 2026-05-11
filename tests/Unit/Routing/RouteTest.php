<?php

namespace Lightningstrike\Tests\Unit\Routing;

use Lightningstrike\Exception\Middleware\InvalidMiddlewareClass;
use Lightningstrike\Exception\Middleware\MiddlewareNotFound;
use Lightningstrike\Exception\Request\InvalidRequestHandlerException;
use Lightningstrike\Exception\Request\RequestHandlerNotFound;
use Lightningstrike\Middleware\AbstractMiddleware;
use Lightningstrike\Request\Request;
use Lightningstrike\Response\Response;
use Lightningstrike\Routing\Route;
use PHPUnit\Framework\TestCase;
use Lightningstrike\Routing\RouteBuilder;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouteBuilder::class)]
class RouteTest extends TestCase
{
    public function testCreateFailureClassNotFound(): void
    {
        $this->expectException(RequestHandlerNotFound::class);
        new Route(Request::METHOD_GET, 'test', 'path', 'nonexistent');
    }

    public function testCreateFailureClassInvalid(): void
    {
        $this->expectException(InvalidRequestHandlerException::class);
        new Route(Request::METHOD_GET, 'test', 'path', Response::class);
    }

    public function testCreateSuccess(): void
    {
        $method = Request::METHOD_GET;
        $name = 'test';
        $path = 'path';
        $handler = AbstractMiddleware::class;

        $route = new Route($method, $name, $path, $handler);

        $this->assertSame($method, $route->method);
        $this->assertSame($name, $route->name);
        $this->assertSame($path, $route->path);
        $this->assertSame($handler, $route->requestHandler);
    }

    public function testAddMiddlewareFailureClassNotFound(): void
    {
        $route = new Route(Request::METHOD_GET, 'test', 'path', AbstractMiddleware::class);
        $this->expectException(MiddlewareNotFound::class);
        $route->addMiddleware('nonexistent');
    }

    public function testAddMiddlewareFailureClassInvalid(): void
    {
        $route = new Route(Request::METHOD_GET, 'test', 'path', AbstractMiddleware::class);
        $this->expectException(InvalidMiddlewareClass::class);
        $route->addMiddleware(Route::class);
    }

    public function testAddMiddlewareSuccess(): void
    {
        $route = new Route(Request::METHOD_GET, 'test', 'path', AbstractMiddleware::class);

        $route->addMiddleware(AbstractMiddleware::class);

        $this->assertSame([AbstractMiddleware::class], $route->middlewareQueue);
    }

    public function testAddPatternSuccess(): void
    {
        $method = Request::METHOD_GET;
        $name = 'test';
        $path = 'path/{id}';
        $handler = AbstractMiddleware::class;

        $route = new Route($method, $name, $path, $handler);

        $route->where('id', '\d+');

        $this->assertSame(['id' => '/\d+/'], $route->paramPatterns);
    }

    public function testAddPatternFailure(): void
    {
        $method = Request::METHOD_GET;
        $name = 'test';
        $path = 'path/{id}';
        $handler = AbstractMiddleware::class;

        $route = new Route($method, $name, $path, $handler);


        $this->expectException(\InvalidArgumentException::class);
        $route->where('id', '/\d+');
    }
}
