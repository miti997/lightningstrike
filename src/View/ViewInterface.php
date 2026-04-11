<?php

namespace Lightningstrike\View;

use Lightningstrike\RequestHandler\RequestHandlerInterface;
use Lightningstrike\Response\ResponseInterface;

interface ViewInterface extends RequestHandlerInterface
{
    public function beforeRender(): void;
    public function render(): ResponseInterface;
    public function afterRender(): void;
}