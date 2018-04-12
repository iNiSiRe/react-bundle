<?php

namespace inisire\ReactBundle\EventDispatcher;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Event;

class AsynchronousEventDispatcher
{
    const STRATEGY_ROUND_ROBIN = 1;

    /**
     * @var DispatcherWorker[]
     */
    private $workers = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $maxWorkers;

    /**
     * @var int
     */
    private $queueDistribution;

    /**
     * @param string $kernelClass
     * @param int    $maxWorkers
     * @param int    $queueDistribution
     */
    public function __construct($kernelClass, $maxWorkers = 1, $queueDistribution = self::STRATEGY_ROUND_ROBIN)
    {
        $this->maxWorkers = $maxWorkers;
        $this->queueDistribution = $queueDistribution;

        for ($i = 0; $i < $maxWorkers; $i++) {
            $this->workers[] = new DispatcherWorker($kernelClass);
        }

        reset($this->workers);
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return DispatcherWorker
     *
     * @throws \Exception
     */
    private function getNextWorker()
    {
        if ($this->queueDistribution != self::STRATEGY_ROUND_ROBIN) {
            throw new \Exception('Strategy not supported');
        }

        if (next($this->workers)) {
            $worker = current($this->workers);
        } else {
            $worker = reset($this->workers);
        }

        return $worker;
    }

    /**
     * @param string $name
     * @param Event|null $event
     *
     * @throws \Exception
     */
    public function dispatch($name, Event $event = null)
    {
        $worker = $this->getNextWorker();

        $worker->synchronized(function ($worker, $name, $event) {

            $worker->addFiredEvent($name, $event);

        }, $worker, $name, $event);
    }

    /**
     * @param string $event
     * @param callable $listener
     *
     * @throws \Exception
     */
    public function addListener($event, callable $listener)
    {
        foreach ($this->workers as $worker) {

            $worker->synchronized(function ($worker, $event, $listener) {

                $worker->addListener($event, $listener);

            }, $worker, $event, $listener);

        }

        reset($this->workers);
    }

    /**
     * Run worker
     */
    public function start()
    {
        foreach ($this->workers as $worker) {
            if (!$worker->isStarted()) {
                $worker->start();
            }
        }

        reset($this->workers);
    }
}
