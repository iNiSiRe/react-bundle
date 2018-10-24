<?php

namespace inisire\ReactBundle\Threaded;

use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;

class PoolMonitor
{
    /**
     * @var LoopInterface
     */
    private $loop;
    /**
     * @var MonitoredPool
     */
    private $pool;

    /**
     * PoolMonitor constructor.
     *
     * @param LoopInterface   $loop
     * @param MonitoredPool   $pool
     * @param LoggerInterface $logger
     */
    public function __construct(LoopInterface $loop, MonitoredPool $pool, LoggerInterface $logger)
    {
        $this->loop = $loop;
        $this->pool = $pool;

        $logger->debug('monitor started');

        $this->loop->addPeriodicTimer(60, [$this, 'monitor']);
    }

    /**
     * Perform Pool maintenance for memory leak prevention
     */
    private function monitor()
    {
        $status = $this->pool->getStatus();

        if ($status['workers'] > 0) {
            $this->pool->collect();
        }

        if (count($status['workers']) > 0 && $status['total']['tasks'] == 0) {
            $this->pool->shutdown();
        }
    }
}