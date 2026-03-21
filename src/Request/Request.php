<?php

namespace Lightningstrike\Request;

use Lightningstrike\Service\HeadersProviderInterface;
use Lightningstrike\Trait\ReadsArrayPaths;

class Request implements RequestInterface
{
    use ReadsArrayPaths;

    public const string METHOD_GET = 'GET';
    public const string METHOD_POST = 'POST';
    private const string COOKIE = 'COOKIE';

    /** @var array<string,mixed> */
    private array $headers = [];

    public function __construct(
        private readonly HeadersProviderInterface $headersProvider
    ) {
    }

    /**
     * @return array<int|string, mixed>
     */
    private function getByPath(string $path, string $requestMethod): string|array|null
    {
        switch ($requestMethod) {
            case self::METHOD_GET:
                $requestData = $_GET;
                break;

            case self::METHOD_POST:
                $requestData = $_POST;
                break;

            case self::COOKIE:
                $requestData = $_COOKIE;
                break;

            default:
                $requestData = [];
                break;
        }

        $result = $this->getByPathFromArray($path, $requestData);
        if (!is_string($result) && !is_array($result)) {
            return null;
        }

        return $result;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getQueryParams(): array
    {
        return $_GET;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getQueryParam(string $path): string|array|null
    {
        return $this->getByPath($path, self::METHOD_GET);
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getBody(): array
    {
        return $_POST;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getBodyParam(string $path): string|array|null
    {
        return $this->getByPath($path, self::METHOD_POST);
    }

    public function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === self::METHOD_GET;
    }

    public function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === self::METHOD_POST;
    }

    /**
     * @return array<string, mixed>
     */
    public function getHeaders(): array
    {
        if ($this->headers !== []) {
            return $this->headers;
        }

        $this->headers = $this->headersProvider->getHeaders();

        return $this->headers;
    }

    public function getHeader(string $name): mixed
    {
        $this->getHeaders();
        $parts = explode('-', strtolower($name));
        $name = implode('-', array_map(fn($p) => ucfirst($p), $parts));

        return $this->headers[$name];
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getCookies(): array
    {
        return $_COOKIE;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getCookie(string $cookie): string|array|null
    {
        return $this->getByPath($cookie, self::COOKIE);
    }

    public function hasCookie(string $cookie): bool
    {
        $cookie = $this->getCookie($cookie);

        return $cookie !== null;
    }
}
