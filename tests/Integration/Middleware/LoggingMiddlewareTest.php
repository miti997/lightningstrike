<?php

namespace Lightningstrike\Tests\Integration\Middleware;

use Exception;
use Lightningstrike\Middleware\LoggingMiddleware;
use Lightningstrike\Request\Request;
use Lightningstrike\RequestHandler\RequestHandlerInterface;
use Lightningstrike\Response\Response;
use Lightningstrike\Service\HeadersProvider;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LoggingMiddleware::class)]
class LoggingMiddlewareTest extends TestCase
{
    private LoggingMiddleware $loggingMiddleware;
    private TestHandler $testHandler;

    #[Override]
    protected function setUp(): void
    {
        $this->testHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($this->testHandler);
        $this->loggingMiddleware = new LoggingMiddleware($logger);
        parent::setUp();
    }

    public function testLoggingSuccessStdoutDebugDisabled(): void
    {
        $mock = $this->createMock(RequestHandlerInterface::class);

        $mock->expects($this->once())
            ->method('handle')
            ->willReturn(new Response(statusCode: Response::HTTP_OK));

        $this->loggingMiddleware->setNext($mock);
        $this->loggingMiddleware->handle(new Request(new HeadersProvider()));

        $this->assertEmpty($this->testHandler->getRecords());
    }

    public function testLoggingSuccessStdoutDebugEnabled(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $mock = $this->createMock(RequestHandlerInterface::class);

        $mock->expects($this->once())
            ->method('handle')
            ->willReturn(new Response(statusCode: Response::HTTP_OK));

        $this->loggingMiddleware->setDebug(true);

        $this->loggingMiddleware->setNext($mock);

        $this->loggingMiddleware->handle(new Request(new HeadersProvider()));

        $records = $this->testHandler->getRecords();

        $this->assertCount(1, $records);

        $record = $records[0];

        $this->assertSame('Request finished', $record['message']);
        $this->assertSame(Level::Debug->value, $record['level']);
        $this->assertIsArray($record['context']);
        $this->assertSame(Response::HTTP_OK, $record['context']['responseStatus']);
        $this->assertSame(Request::METHOD_GET, $record['context']['request']);
        $this->assertNotEmpty($record['context']['duration']);
    }

    public function testLoggingSlowRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $mock = $this->createMock(RequestHandlerInterface::class);

        $mock->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function () {
                sleep(1);
                return new Response(statusCode: Response::HTTP_OK);
            });

        $this->loggingMiddleware->setNext($mock);
        $this->loggingMiddleware->setSlowRequestThreshold(1);

        $this->loggingMiddleware->handle(new Request(new HeadersProvider()));

        $records = $this->testHandler->getRecords();

        $this->assertCount(1, $records);

        $record = $records[0];

        $this->assertSame('Slow request', $record['message']);
        $this->assertSame(Level::Warning->value, $record['level']);
        $this->assertIsArray($record['context']);
        $this->assertSame(Response::HTTP_OK, $record['context']['responseStatus']);
        $this->assertSame(Request::METHOD_GET, $record['context']['request']);
        $this->assertNotEmpty($record['context']['duration']);
    }

    public function testLogging400Response(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $mock = $this->createMock(RequestHandlerInterface::class);

         $mock->expects($this->once())
            ->method('handle')
            ->willReturn(new Response(statusCode: Response::HTTP_NOT_FOUND));

        $this->loggingMiddleware->setNext($mock);
        $this->loggingMiddleware->setSlowRequestThreshold(1);

        $this->loggingMiddleware->handle(new Request(new HeadersProvider()));

        $records = $this->testHandler->getRecords();

        $this->assertCount(1, $records);

        $record = $records[0];

        $this->assertSame('Request error', $record['message']);
        $this->assertSame(Level::Warning->value, $record['level']);
        $this->assertIsArray($record['context']);
        $this->assertSame(Response::HTTP_NOT_FOUND, $record['context']['responseStatus']);
        $this->assertSame(Request::METHOD_GET, $record['context']['request']);
        $this->assertNotEmpty($record['context']['duration']);
    }

    public function testLogging500Response(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $mock = $this->createMock(RequestHandlerInterface::class);

         $mock->expects($this->once())
            ->method('handle')
            ->willReturn(new Response(statusCode: Response::HTTP_INTERNAL_SERVER_ERROR));

        $this->loggingMiddleware->setNext($mock);
        $this->loggingMiddleware->setSlowRequestThreshold(1);

        $this->loggingMiddleware->handle(new Request(new HeadersProvider()));

        $records = $this->testHandler->getRecords();

        $this->assertCount(1, $records);

        $record = $records[0];

        $this->assertSame('Server error', $record['message']);
        $this->assertSame(Level::Error->value, $record['level']);
        $this->assertIsArray($record['context']);
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $record['context']['responseStatus']);
        $this->assertSame(Request::METHOD_GET, $record['context']['request']);
        $this->assertNotEmpty($record['context']['duration']);
    }

    public function testLoggingUncaughtErrror(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $mock = $this->createMock(RequestHandlerInterface::class);

         $mock->expects($this->once())
            ->method('handle')
            ->willThrowException(new Exception());

        $this->loggingMiddleware->setNext($mock);
        $this->loggingMiddleware->setSlowRequestThreshold(1);

        try {
            $this->loggingMiddleware->handle(new Request(new HeadersProvider()));
        } catch (\Throwable $th) {
            $this->assertInstanceOf(Exception::class, $th);
        }

        $records = $this->testHandler->getRecords();

        $this->assertCount(1, $records);

        $record = $records[0];

        $this->assertSame('Uncaught Error', $record['message']);
        $this->assertSame(Level::Error->value, $record['level']);
        $this->assertIsArray($record['context']);
        $this->assertSame(null, $record['context']['responseStatus']);
        $this->assertSame(Request::METHOD_GET, $record['context']['request']);
        $this->assertNotEmpty($record['context']['duration']);
    }
}
