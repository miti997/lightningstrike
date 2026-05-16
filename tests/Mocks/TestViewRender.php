<?php

namespace Lightningstrike\Tests\Mocks;

use Lightningstrike\Response\Response;
use Lightningstrike\Response\ResponseInterface;
use Lightningstrike\View\AbstractView;

class TestViewRender extends AbstractView
{
    /**
     * @param array<string,mixed> $data
     */
    public function __construct(private string $template, private array $data = [])
    {
    }

    public function render(): ResponseInterface
    {
        $response = new Response();
        $response->setBody((string)$this->renderTemplate(
            $this->template,
            $this->data
        ));

        return $response;
    }
}
