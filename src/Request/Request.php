<?php

namespace Lightningstrike\Request;

class Request implements RequestInterface
{
    public const string METHOD_GET = 'GET';
    public const string METHOD_POST = 'POST';
    private const string COOKIE = 'COOKIE';

    /** @var array<string,mixed> */
    private array $headers = [];

    /** @phpstan-ignore-next-line */
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

        if ($requestData === []) {
            return null;
        }

        $params = explode('.', $path);

        $paramValue = null;

        foreach ($params as $param) {
            $param = is_numeric($param) ? (int) $param : $param;

            /** @phpstan-ignore-next-line */
            $paramValue = $requestData[$param] ?? null;

            if ($paramValue === null) {
                break;
            }

            $requestData = $requestData[$param];
        }

        /** @phpstan-ignore-next-line */
        return $paramValue;
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

        $this->headers = function_exists('\getallheaders') ? \getallheaders() : $this->getHeadersFromServer();

        return $this->headers;
    }

    /**
     * @return array<string, mixed>
     */
    private function getHeadersFromServer(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', substr($key, 5));
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'])) {
                $name = str_replace('_', '-', $key);
            } else {
                continue;
            }

            $parts = explode('-', strtolower($name));
            $name = implode('-', array_map(fn($p) => ucfirst($p), $parts));
            $headers[$name] = $value;
        }

        return $headers;
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
