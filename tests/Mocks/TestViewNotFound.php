<?php

namespace Lightningstrike\Tests\Mocks;

use Lightningstrike\Response\Response;
use Lightningstrike\Response\ResponseInterface;
use Lightningstrike\View\AbstractView;
use Override;

class TestViewNotFound extends AbstractView
{
    #[Override]
    public function render(): ResponseInterface
    {
        return new Response(body: 'Not found', statusCode: 404);
    }
}