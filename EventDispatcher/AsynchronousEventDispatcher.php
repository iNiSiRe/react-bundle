<?php

namespace inisire\ReactBundle\EventDispatcher;

use inisire\ReactBundle\EventDispatcher\Listener\EventListener;
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
     * @param KernelFactoryInterface $kernelFactory
     * @param int                    $maxWorkers
     * @param int                    $queueDistribution * @param array $workerKernelParameters
     */
    public function __construct(KernelFactoryInterface $kernelFactory, $maxWorkers = 1, $queueDistribution = self::STRATEGY_ROUND_ROBIN)
    {
        $this->maxWorkers = $maxWorkers;
        $this->queueDistribution = $queueDistribution;

        for ($i = 0; $i < $maxWorkers; $i++) {
            $this->workers[] = new DispatcherWorker($i + 1, $kernelFactory);
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
     * @param string     $name
     * @param Event|null $event
     *
     * @throws \Exception
     */
    public function dispatch($name, $event = null)
    {
        $worker = $this->getNextWorker();

        if ($worker->isTerminated()) {

            if ($this->logger) {
                $this->logger->debug('DispatcherWorker::terminated -> start');
            }

            $worker->start();
        }

        $worker->synchronized(function ($worker, $name, $event) {

            /**
             * @var DispatcherWorker $worker
             */
            $worker->addFiredEvent($name, $event);

        }, $worker, $name, $event);
    }

    /**
     * @param string   $event
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
     * @param iterable|EventListener[] $listeners
     *
     * @throws \Exception
     */
    public function addListeners(iterable $listeners)
    {
        foreach ($listeners as $listener) {
            $this->addListener($listener->getEvent(), [$listener, 'onEvent']);
        }
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

    /**
     * @return array
     */
    public function getStatus()
    {
        $status = [];

        foreach ($this->workers as $worker) {
            $status[] = [
                'running' => $worker->isRunning(),
                'terminated' => $worker->isTerminated(),
                'waited' => $worker->isWaiting(),
                'started' => $worker->isStarted()
            ];
        }

        reset($this->workers);

        return $status;
    }
}
