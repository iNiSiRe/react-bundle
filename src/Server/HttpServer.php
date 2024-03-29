<?php

namespace inisire\ReactBundle\Server;

use inisire\ReactBundle\HttpFoundation\PromiseResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Http\Server as ReactHttpServer;
use React\Promise\PromiseInterface;
use React\Socket\Server as ReactSocketServer;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use function React\Promise\resolve;

class HttpServer
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var ReactSocketServer
     */
    private $socketServer;

    /**
     * @var ReactHttpServer
     */
    private $httpServer;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var HttpFoundationFactoryInterface
     */
    private $foundationFactory;

    /**
     * @var HttpMessageFactoryInterface
     */
    private $httpMessageFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Server constructor.
     *
     * @param LoopInterface                  $loop
     * @param ReactSocketServer              $socketServer
     * @param HttpKernelInterface            $kernel
     * @param HttpFoundationFactoryInterface $foundationFactory
     * @param HttpMessageFactoryInterface    $httpMessageFactory
     * @param LoggerInterface                $logger
     */
    public function __construct(LoopInterface $loop, ReactSocketServer $socketServer, HttpKernelInterface $kernel,
                                HttpFoundationFactoryInterface $foundationFactory, HttpMessageFactoryInterface $httpMessageFactory,
                                LoggerInterface $logger)
    {
        $this->loop = $loop;
        $this->socketServer = $socketServer;

        $this->httpServer = new ReactHttpServer(
            $loop,
            [$this, 'handleRequest']
        );

        $this->kernel = $kernel;
        $this->foundationFactory = $foundationFactory;
        $this->httpMessageFactory = $httpMessageFactory;
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
     *
     * @return ResponseInterface|PromiseInterface
     */
    public function handleRequest(ServerRequestInterface $request)
    {
        $sfRequest = $this->foundationFactory->createRequest($request);

        try {
            $sfResponse = $this->kernel->handle($sfRequest);
        } catch (HttpException $exception) {
            $this->logRequestError($request, $exception);
            $sfResponse = new Response($exception->getMessage(), $exception->getStatusCode(), $exception->getHeaders());
        } catch (\Throwable $exception) {
            $this->logRequestError($request, $exception);
            $sfResponse = new Response($exception->getMessage(), 500);
        }

        if ($sfResponse instanceof PromiseResponse) {
            $promise = $sfResponse->getPromise();
        } else {
            $promise = resolve($sfResponse);
        }

        return $promise
            ->then(
                function ($sfResponse) use ($sfRequest) {
                    if ((bool) $_ENV['REACT_ADD_CORS_HEADERS'] ?? true) {
                        $sfResponse->headers->add(['Access-Control-Allow-Origin' => '*']);
                    }
                    $response = $this->httpMessageFactory->createResponse($sfResponse);
                    $this->kernel->terminate($sfRequest, $sfResponse);
                    return $response;
                },
                function (\Error $error) {
                    return new \React\Http\Message\Response(500, [], $error->getMessage());
                }
            );
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