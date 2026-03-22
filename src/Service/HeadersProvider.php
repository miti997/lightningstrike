<?php

namespace Lightningstrike\Service;

class HeadersProvider implements HeadersProviderInterface
{
    /** @var array<string,mixed> */
    private array $headers = [];

    /**
     * @return array<string, mixed>
     */
    public function getHeaders(): array
    {
        if ($this->headers !== []) {
            return $this->headers;
        }

        if (function_exists('\getallheaders')) {
            return \getallheaders();
        }

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
}
