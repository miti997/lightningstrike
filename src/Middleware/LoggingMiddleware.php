<?php

namespace Lightningstrike\Middleware;

use Lightningstrike\Request\RequestInterface;
use Lightningstrike\RequestHandler\RequestHandlerInterface;
use Lightningstrike\Response\ResponseInterface;
use Override;
use Psr\Log\LoggerInterface;

class LoggingMiddleware extends AbstractMiddleware
{
    public function __construct(
        private LoggerInterface $logger,
        private bool $debug = false,
        private int $slowRequestThreshold = 60,
    ) {
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    public function setSlowRequestThreshold(int $treshold): void
    {
        $this->slowRequestThreshold = $treshold;
    }

    #[Override]
    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $startTime = microtime(true);

        try {
            $response = $next->handle($request);
        } catch (\Throwable $th) {
            $this->logger->error('Uncaught Error', [
                'request' => $request->getRequestMethod(),
                'uri' => $request->getUri(),
                'duration' => microtime(true) - $startTime,
                'responseStatus' => null,
                'error' => $th->getMessage(),
            ]);

            throw $th;
        }

        $duration  = microtime(true) - $startTime;

        $context = [
            'request' => $request->getRequestMethod(),
            'uri' => $request->getUri(),
            'duration' => $duration,
            'responseStatus' => $response->getStatusCode(),
        ];

        if ($this->debug) {
            $this->logger->debug('Request finished', $context);
        }

        if ($duration > $this->slowRequestThreshold) {
            $this->logger->warning('Slow request', $context);
        }

        if ($response->getStatusCode() < 500 && $response->getStatusCode() >= 400) {
            $this->logger->warning('Request error', $context);
        }

        if ($response->getStatusCode() >= 500) {
            $this->logger->error('Server error', $context);
        }

        return $response;
    }
}
