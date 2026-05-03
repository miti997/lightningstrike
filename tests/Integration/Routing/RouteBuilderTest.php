<?php

namespace Lightningstrike\Tests\Integration\Routing;

use Lightningstrike\Middleware\AbstractMiddleware;
use Lightningstrike\Request\Request;
use PHPUnit\Framework\TestCase;
use Lightningstrike\Routing\RouteBuilder;
use Lightningstrike\Script\AbstractScript;
use Lightningstrike\View\AbstractView;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouteBuilder::class)]
class RouteBuilderTest extends TestCase
{
    private RouteBuilder $routeBuilder;

    private const string CACHE_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'cache';

    protected function setUp(): void
    {
        parent::setUp();
        $this->routeBuilder = new RouteBuilder();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->deleteDirectory(self::CACHE_DIR);
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS) as $item) {
            if (!$item instanceof \SplFileInfo) {
                continue;
            }

            $item->isDir()
                ? $this->deleteDirectory($item->getPathname())
                : unlink($item->getPathname());
        }

        rmdir($dir);
    }

    public function testBuildRouteTree(): void
    {
        $this->routeBuilder->get('/', AbstractView::class);
        $this->routeBuilder->get('/{id}', AbstractView::class);
        $this->routeBuilder->get('/{id}/test', AbstractView::class);
        $this->routeBuilder->get('/{id}/{secondId}', AbstractView::class);
        $this->routeBuilder->get('/products', AbstractView::class);
        $this->routeBuilder->get('/products/{id}', AbstractView::class);
        $this->routeBuilder->get('/users', AbstractView::class);
        $this->routeBuilder->post('/users', AbstractScript::class)
            ->addMiddleware(AbstractMiddleware::class);
        $this->routeBuilder->post('/users/{id}', AbstractScript::class)
            ->addMiddleware(AbstractMiddleware::class)
            ->where('id', '\d+');


        $this->assertSame(
            [
                Request::METHOD_GET => [
                    RouteBuilder::FIXED => [
                        'products' => [
                            RouteBuilder::FIXED => [],
                            RouteBuilder::DYNAMIC => [
                                'id' => [
                                    RouteBuilder::FIXED => [],
                                    RouteBuilder::DYNAMIC => [],
                                    RouteBuilder::HANDLER => AbstractView::class,
                                    RouteBuilder::PATTERN => null,
                                    RouteBuilder::MIDDLEWARE => [],
                                ],
                            ],
                            RouteBuilder::HANDLER => AbstractView::class,
                            RouteBuilder::MIDDLEWARE => [],
                        ],
                        'users' => [
                            RouteBuilder::FIXED => [],
                            RouteBuilder::DYNAMIC => [],
                            RouteBuilder::HANDLER => AbstractView::class,
                            RouteBuilder::MIDDLEWARE => [],
                        ],
                    ],
                    RouteBuilder::DYNAMIC => [
                        'id' => [
                            RouteBuilder::FIXED => [
                                'test' => [
                                    RouteBuilder::FIXED => [],
                                    RouteBuilder::DYNAMIC => [],
                                    RouteBuilder::HANDLER => AbstractView::class,
                                    RouteBuilder::MIDDLEWARE => [],
                                ],
                            ],
                            RouteBuilder::DYNAMIC => [
                                'secondId' => [
                                    RouteBuilder::FIXED => [],
                                    RouteBuilder::DYNAMIC => [],
                                    RouteBuilder::HANDLER => AbstractView::class,
                                    RouteBuilder::PATTERN => null,
                                    RouteBuilder::MIDDLEWARE => [],
                                ],
                            ],
                            RouteBuilder::HANDLER => AbstractView::class,
                            RouteBuilder::PATTERN => null,
                            RouteBuilder::MIDDLEWARE => [],
                        ],
                    ],
                    RouteBuilder::HANDLER => AbstractView::class,
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
            ],
            $this->routeBuilder->buildRouteTree()
        );
    }

    public function testCacheTree(): void
    {
        $path = self::CACHE_DIR . DIRECTORY_SEPARATOR . 'routes.php';
        $tree = [
            Request::METHOD_GET => [],
            Request::METHOD_POST => [],
        ];
        $this->routeBuilder->cacheTree($path, $tree);

        $cache = require $path;

        $this->assertSame($tree, $cache);
    }
}
