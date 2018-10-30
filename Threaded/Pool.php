<?php

namespace inisire\ReactBundle\Threaded;

use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;

class Pool extends \Pool
{
    private $id = 0;

    private $callbacks = [];

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Task     $task
     * @param callable $callback
     *
     * @return int
     */
    public function submit($task, callable $callback = null)
    {
        $id = $this->id++;

        $task->setId($id);

        if ($callback) {
            $this->callbacks[$id] = $callback;
        }

        return parent::submit($task);
    }

    /**
     * @return array
     */
    public function getStatus()
    {
        $workers = [];
        $tasks = 0;

        if ($this->workers) {

            /**
             * @var \Worker $worker
             */
            foreach ($this->workers as $worker) {

                $tasks += $worker->getStacked();

                $workers[] = [
                    'started' => $worker->isStarted(),
                    'running' => $worker->isRunning(),
                    'tasks' => $worker->getStacked(),
                    'terminated' => $worker->isTerminated(),
                    'shutdown' => $worker->isShutdown()
                ];
            }

        }

        $status = [
            'workers' => count($workers),
            'tasks' => $tasks,
            'memory' => [
                'current' => [
                    'real' => round(memory_get_usage(true) / 1024 / 1024, 2),
                    'reserved' => round(memory_get_usage(false) / 1024 / 1024, 2)
                ],
                'peak' => [
                    'real' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                    'reserved' => round(memory_get_peak_usage(false) / 1024 / 1024, 2)
                ]
            ]
        ];

        return $status;
    }

    /**
     * @return bool
     */
    public function isStarted()
    {
        $workers = is_null($this->workers) ? 0 : count($this->workers);

        return $workers > 0;
    }

    /**
     * @return int
     */
    public function getWaitingTasks()
    {
        $count = 0;

        foreach ($this->workers as $worker) {
            $count += $worker->getStacked();
        }

        return $count;
    }

    public function isRunning()
    {
        if (!$this->workers) {
            return false;
        }

        foreach ($this->workers as $worker) {

            if ($worker->isRunning()) {
                return true;
            }

        }
    }

    /**
     * Collect tasks results
     *
     * @return int
     */
    private function doCollect()
    {
        return $this->collect(function ($task) {

            if (!$task->isCompleted()) {
                return false;
            }

            $callback = $this->callbacks[$task->getId()] ?? null;

            if ($callback) {
                unset($this->callbacks[$task->getId()]);
                $callback($task->getResult());
            }

            return true;

        });
    }

    /**
     * @param LoopInterface $loop
     *
     * @return $this
     */
    public function setLoop($loop)
    {
        $this->loop = $loop;

        $loop->addPeriodicTimer(0.5, function () {

            $remain = $this->doCollect();

            while ($remain > 0) {

                $this->loop->futureTick(function () {
                    $this->doCollect();
                });

                $remain--;

            }
        });


        return $this;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}