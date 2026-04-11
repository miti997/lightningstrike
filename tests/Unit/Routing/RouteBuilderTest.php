<?php

namespace Lightningstrike\Tests\Unit\Response;

use Lightningstrike\Exception\Script\InvalidScriptClass;
use Lightningstrike\Exception\Script\ScriptClassNotFound;
use Lightningstrike\Exception\View\InvalidViewClass;
use Lightningstrike\Exception\View\ViewClassNotFound;
use Lightningstrike\Request\Request;
use Lightningstrike\Routing\Route;
use PHPUnit\Framework\TestCase;
use Lightningstrike\Routing\RouteBuilder;
use Lightningstrike\Script\AbstractScript;
use Lightningstrike\View\AbstractView;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouteBuilder::class)]
class RouteBuilderTest extends TestCase
{
    private RouteBuilder $routeBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->routeBuilder = new RouteBuilder();
    }

    public function testGetSuccess(): void
    {
        $name1 = 'name1';
        $path1 = '/products';

        $name2 = 'name2';
        $path2 = '/orders';
        $view1 = $view2 = AbstractView::class;

        $route1 = $this->routeBuilder->get(name: $name1, path: $path1, view: $view1);

        $route2 = $this->routeBuilder->get(name: $name2, path: $path2, view: $view2);

        $this->assertSame($name2, $route2->name);
        $this->assertSame($path2, $route2->path);
        $this->assertSame($view2, $route2->requestHandler);
        $this->assertSame(Request::METHOD_GET, $route2->method);

        $this->assertSame($name1, $route1->name);
        $this->assertSame($path1, $route1->path);
        $this->assertSame($view1, $route1->requestHandler);
        $this->assertSame(Request::METHOD_GET, $route1->method);
    }

    public function testGetNonExistentViewClass(): void
    {
        $name = 'name';
        $path = '/products';
        $view = 'nonexistent';

        $this->expectException(ViewClassNotFound::class);

        $this->routeBuilder->get(name: $name, path: $path, view: $view);
    }

    public function testGetInvalidViewClass(): void
    {
        $name = 'name';
        $path = '/products';
        $view = Route::class;

        $this->expectException(InvalidViewClass::class);

        $this->routeBuilder->get(name: $name, path: $path, view: $view);
    }

    public function testPostSuccess(): void
    {
        $name1 = 'name1';
        $path1 = '/products';

        $name2 = 'name2';
        $path2 = '/orders';
        $script1 = $script2 = AbstractScript::class;

        $route1 = $this->routeBuilder->post(name: $name1, path: $path1, script: $script1);

        $route2 = $this->routeBuilder->post(name: $name2, path: $path2, script: $script2);

        $this->assertSame($name2, $route2->name);
        $this->assertSame($path2, $route2->path);
        $this->assertSame($script2, $route2->requestHandler);
        $this->assertSame(Request::METHOD_GET, $route2->method);

        $this->assertSame($name1, $route1->name);
        $this->assertSame($path1, $route1->path);
        $this->assertSame($script1, $route1->requestHandler);
        $this->assertSame(Request::METHOD_GET, $route1->method);
    }

    public function testPostNonExistentViewClass(): void
    {
        $name = 'name';
        $path = '/products';
        $script = 'nonexistent';

        $this->expectException(ScriptClassNotFound::class);

        $this->routeBuilder->post(name: $name, path: $path, script: $script);
    }

    public function testPostInvalidViewClass(): void
    {
        $name = 'name';
        $path = '/products';
        $script = Route::class;

        $this->expectException(InvalidScriptClass::class);

        $this->routeBuilder->post(name: $name, path: $path, script: $script);
    }
}
