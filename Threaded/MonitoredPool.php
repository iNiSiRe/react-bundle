<?php

namespace inisire\ReactBundle\Threaded;

class MonitoredPool extends \Pool
{
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
            'workers' => $workers,
            'total' => [
                'tasks' => $tasks
            ]
        ];

        return $status;
    }
}