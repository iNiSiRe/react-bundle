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
     * @param DispatcherWorker $worker
     * @param LoggerInterface  $logger
     */
    public function __construct(DispatcherWorker $worker, LoggerInterface $logger)
    {
        $this->worker = $worker;
        $this->logger = $logger;
    }

    /**
     * @param string     $name
     * @param Event|null $event
     */
    public function dispatch($name, Event $event = null)
    {
        $this->worker->addFiredEvent($name, $event);
    }

    /**
     * @param string   $event
     * @param callable $listener
     */
    public function addListener($event, callable $listener)
    {
        $this->worker->addListener($event, $listener);
    }
}
