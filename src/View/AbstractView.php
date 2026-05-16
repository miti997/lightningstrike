<?php

namespace Lightningstrike\View;

use Lightningstrike\Exception\View\TemplateNotFound;
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

    /**
     * @param array<string,mixed> $data
     */
    protected function renderTemplate(string $template, array $data = []): string|false
    {
        if (!file_exists($template)) {
            throw new TemplateNotFound($template);
        }

        \extract($data);

        \ob_start();

        include $template;

        return \ob_get_clean();
    }
}
