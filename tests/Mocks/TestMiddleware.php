<?php

namespace Lightningstrike\Tests\Mocks;

use Lightningstrike\Middleware\AbstractMiddleware;
use Lightningstrike\Request\RequestInterface;
use Lightningstrike\RequestHandler\RequestHandlerInterface;
use Lightningstrike\Response\Response;
use Lightningstrike\Response\ResponseInterface;
use Override;

class TestMiddleware extends AbstractMiddleware
{
    #[Override]
    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $response = $next->handle($request);

        $response->setStatusCode(Response::HTTP_FOUND);

        return $response;
    }
}
