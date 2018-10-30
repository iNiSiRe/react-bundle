<?php

namespace inisire\ReactBundle\Threaded;

class WorkerPoolFactory
{
    private $loader;

    private $kernelFactory;

    /**
     * @var int
     */
    private $workersCount;

    /**
     * @param     $loader
     * @param     $kernelFactory
     * @param int $workersCount
     */
    public function __construct($loader, $kernelFactory, $workersCount = 1)
    {
        $this->loader = $loader;
        $this->kernelFactory = $kernelFactory;
        $this->workersCount = $workersCount;
    }

    public function create()
    {
        return new Pool($this->workersCount, Worker::class, [$this->loader, $this->kernelFactory]);
    }
}