<?php

namespace inisire\ReactBundle\Server;

use inisire\ReactBundle\Middleware\UploadedFilesProcessor;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Http\Io\MiddlewareRunner;
use React\Http\Middleware\RequestBodyBufferMiddleware;
use React\Http\Middleware\RequestBodyParserMiddleware;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Server
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var SocketServer
     */
    private $socketServer;

    /**
     * @var HttpServer
     */
    private $httpServer;

    /**
     * @var App\Kernel
     */
    private $kernel;

    /**
     * @var HttpFoundationFactoryInterface
     */
    private $foundationFactory;

    /**
     * @var DiactorosFactory
     */
    private $diactorosFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Server constructor.
     *
     * @param LoopInterface                  $loop
     * @param SocketServer                   $socketServer
     * @param HttpKernelInterface            $kernel
     * @param HttpFoundationFactoryInterface $foundationFactory
     * @param DiactorosFactory               $diactorosFactory
     * @param Logger                         $logger
     */
    public function __construct(LoopInterface $loop, SocketServer $socketServer, HttpKernelInterface $kernel,
                                HttpFoundationFactoryInterface $foundationFactory, DiactorosFactory $diactorosFactory,
                                LoggerInterface $logger)
    {
        $this->loop = $loop;
        $this->socketServer = $socketServer;

        $this->httpServer = new HttpServer(new MiddlewareRunner([
            new UploadedFilesProcessor($this->loop),
            [$this, 'handleRequest']]
        ));

        $this->kernel = $kernel;
        $this->foundationFactory = $foundationFactory;
        $this->diactorosFactory = $diactorosFactory;
        $this->logger = $logger;
    }

    /**
     * @param Request  $request
     * @param Response $response
     */
    protected function logRequest(Request $request, Response $response)
    {
        $message = \sprintf(
            '%s - [%s] "%s %s" %s %s',
            $request->getClientIp(),
            (new \DateTime())->format('d/M/Y H:i:s O'),
            $request->getRealMethod(),
            $request->getUri(),
            $response->getStatusCode(),
            \strlen($response->getContent())
        );

        $this->logger->info($message);
    }

    /**
     * @param ServerRequestInterface $request
     * @param \Throwable             $error
     */
    protected function logRequestError(ServerRequestInterface $request, \Throwable $error)
    {
        $message = \sprintf(
            '%s - %s in %s (%s)',
            $request->getServerParams()['REMOTE_ADDR'],
            $error->getMessage(),
            $error->getFile(),
            $error->getLine()
        );

        $this->logger->error($message);
    }

    /**
     * @param \Throwable $error
     */
    protected function logError(\Throwable $error)
    {
        $message = \sprintf(
            '%s in %s (%s)',
            $error->getMessage(),
            $error->getFile(),
            $error->getLine()
        );

        $this->logger->error($message);
    }

    /**
     * @param ServerRequestInterface $request
     * @param callable               $next
     *
     * @return ResponseInterface
     */
    public function handleRequest(ServerRequestInterface $request, callable $next)
    {
        try {
            $sfRequest = $this->foundationFactory->createRequest($request);
            $sfResponse = $this->kernel->handle($sfRequest);

            $response = $this->diactorosFactory->createResponse($sfResponse);

            $this->kernel->terminate($sfRequest, $sfResponse);
        } catch (HttpException $exception) {
            $this->logRequestError($request, $exception);

            return new \React\Http\Response($exception->getStatusCode(), $exception->getHeaders(), $exception->getMessage());
        } catch (\Throwable $exception) {
            $this->logRequestError($request, $exception);
            return new \React\Http\Response(500, [], $exception->getMessage());
        }

        return $response;
    }

    /**
     * Starts socket listening
     */
    public function start()
    {
        $this->httpServer->on('error', function ($exception) {
            $this->logError($exception);
        });

        $this->httpServer->listen($this->socketServer);
    }
}