<?php

namespace Lightningstrike\Request;

interface RequestInterface
{
    /**
     * @return array<int|string, mixed>
     */
    public function getQueryParams(): array;

    /**
     * @return array<int|string, mixed>
     */
    public function getQueryParam(string $path): string|array|null;

    /**
     * @return array<int|string, mixed>
     */
    public function getBody(): array;

    /**
     * @return array<int|string, mixed>
     */
    public function getBodyParam(string $path): string|array|null;

    public function isGet(): bool;

    public function isPost(): bool;

    /**
     * @return array<string, mixed>
     */
    public function getHeaders(): array;

    public function getHeader(string $name): mixed;

    /**
     * @return array<int|string, mixed>
     */
    public function getCookies(): array;

     /**
     * @return array<int|string, mixed>
     */
    public function getCookie(string $cookie): string|array|null;

    public function hasCookie(string $cookie): bool;
}
