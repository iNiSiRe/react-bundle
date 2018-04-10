<?php

namespace inisire\ReactBundle\EventDispatcher;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Event;

class AsynchronousEventDispatcher
{
    /**
     * @var DispatcherWorker
     */
    private $worker;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AsynchronousEventDispatcher constructor.
     *
     * @param string          $kernelClass
     * @param LoggerInterface $logger
     */
    public function __construct($kernelClass, LoggerInterface $logger)
    {
        $this->worker = new DispatcherWorker($kernelClass, $logger);
        $this->logger = $logger;
    }

    /**
     * @param string     $name
     * @param Event|null $event
     */
    public function dispatch($name, Event $event = null)
    {
        if ($this->worker->isTerminated()) {
            $this->logger->debug('DispatcherWorker::terminated start');
            $this->worker->start();
        }

        $this->worker->synchronized(function ($worker, $name, $event) {

            $worker->addFiredEvent($name, $event);

        }, $this->worker, $name, $event);
    }

    /**
     * @param string   $event
     * @param callable $listener
     */
    public function addListener($event, callable $listener)
    {
        $this->worker->synchronized(function ($worker, $event, $listener) {

            $worker->addListener($event, $listener);

        }, $this->worker, $event, $listener);
    }

    /**
     * Run worker
     */
    public function start()
    {
        if (!$this->worker->isStarted()) {
            $this->worker->start();
        }
    }
}
