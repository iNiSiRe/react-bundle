<?php

namespace inisire\ReactBundle\Threaded;

class WorkerPoolFactory
{
    private $loader;
    private $kernelFactory;

    public function __construct($loader, $kernelFactory)
    {
        $this->loader = $loader;
        $this->kernelFactory = $kernelFactory;
    }

    public function create()
    {
        return new MonitoredPool(1, Worker::class, [$this->loader, $this->kernelFactory]);
    }
}