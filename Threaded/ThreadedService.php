<?php

namespace inisire\ReactBundle\Threaded;


class ThreadedService implements ThreadedServiceInterface
{
    const MODE_RUN_IN_CURRENT_THREAD = 1;
    const MODE_RUN_IN_WORKER_THREAD = 2;

    /**
     * @var MonitoredPool
     */
    protected $pool;

    /**
     * @var int
     */
    protected $mode = self::MODE_RUN_IN_CURRENT_THREAD;

    /**
     * @param MonitoredPool $pool
     */
    public function setPool(MonitoredPool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @param int $number
     */
    public function setThreadNumber(int $number)
    {
        $this->mode = $number == ThreadedKernelInterface::MAIN_THREAD
            ? ThreadedService::MODE_RUN_IN_WORKER_THREAD
            : ThreadedService::MODE_RUN_IN_CURRENT_THREAD;
    }
}