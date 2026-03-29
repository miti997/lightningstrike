<?php

namespace Lightningstrike\Response;

interface ResponseInterface
{
    public function send(): void;

    public function setBody(string $body): void;

    public function getBody(): string;

    public function setHeader(string $header, string|int|bool $value): void;

    public function getHeader(string $header): string;

    /** @return array<string,string> */
    public function getHeaders(): array;

    public function setStatusCode(int $statusCode): void;

    public function getStatusCode(): int;
}
