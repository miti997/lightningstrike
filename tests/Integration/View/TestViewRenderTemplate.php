<?php

namespace Lightningstrike\Tests\Integration\View;

use Lightningstrike\Exception\View\TemplateNotFound;
use Lightningstrike\Tests\Mocks\TestViewRender;
use Lightningstrike\View\AbstractView;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractView::class)]
class TestViewRenderTemplate extends TestCase
{
    public function testRenderTemplateSuccess(): void
    {
        $view = new TestViewRender(__DIR__ . '/../../templates/test_template.php', ['testData' => 'test']);

        $response = $view->render();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('TESTtest', $response->getBody());
    }

    public function testRenderTemplateFailure(): void
    {
        $view = new TestViewRender(__DIR__ . '/../../templates/none.php');

        $this->expectException(TemplateNotFound::class);
        $view->render();
    }
}
