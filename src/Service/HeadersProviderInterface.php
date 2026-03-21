<?php

namespace Lightningstrike\Service;

interface HeadersProviderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getHeaders(): array;
}
