<?php

namespace inisire\ReactBundle\EventDispatcher;

use Doctrine\ORM\EntityManagerInterface;
use inisire\ReactBundle\EventDispatcher\Proxy\EntityManagerProxy;
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
