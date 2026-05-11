<?php

namespace Lightningstrike\Tests\Mocks;

use Lightningstrike\Request\RequestInterface;
use Lightningstrike\Response\Response;
use Lightningstrike\Response\ResponseInterface;
use Lightningstrike\Script\AbstractScript;
use Override;

class TestScript extends AbstractScript
{
    #[Override]
    public function process(RequestInterface $request): ResponseInterface
    {
        return new Response(body: 'Success');
    }
}
