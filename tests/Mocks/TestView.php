<?php

namespace Lightningstrike\Tests\Mocks;

use Lightningstrike\Response\Response;
use Lightningstrike\Response\ResponseInterface;
use Lightningstrike\View\AbstractView;
use Override;

class TestView extends AbstractView
{
    #[Override]
    public function render(): ResponseInterface
    {
        return new Response(body: 'Success');
    }
}
