<?php

namespace Lightningstrike\View;

use Lightningstrike\Request\RequestInterface;
use Lightningstrike\Response\ResponseInterface;

abstract class AbstractView implements ViewInterface
{
    public function beforeRender(): void
    {
        return;
    }

    public function afterRender(): void
    {
        return;
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        $this->beforeRender();
        $response = $this->render();
        $this->afterRender();

        return $response;
    }
}