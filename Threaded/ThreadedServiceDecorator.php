<?php

namespace inisire\ReactBundle\Threaded;

class ThreadedServiceDecorator
{
    /**
     * @var object
     */
    private $serviceName;

    /**
     * @var MonitoredPool
     */
    private $pool;

    /**
     * ServiceDecorator constructor.
     *
     * @param object        $serviceName
     * @param MonitoredPool $pool
     */
    public function __construct($serviceName, MonitoredPool $pool)
    {
        $this->serviceName = $serviceName;
        $this->pool = $pool;
    }

    /**
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        $this->pool->submit(new ServiceMethodCall($this->serviceName, $name, $arguments));
    }
}