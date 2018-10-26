<?php


namespace inisire\ReactBundle\Threaded;


class ServiceMethodCall extends \Threaded
{
    /**
     * @var string
     */
    private $service;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @param string $service
     * @param string $method
     * @param array  $arguments
     */
    public function __construct($service, $method, array $arguments = [])
    {
        $this->service = $service;
        $this->method = $method;
        $this->arguments = $arguments;
    }

    /**
     * Execute task
     */
    public function run()
    {
        if (Kernel::isLoaded()) {
            $kernel = Kernel::getInstance();
        } else {
            throw new \RuntimeException('Kernel is not loaded');
        }

        $container = $kernel->getContainer();

        if ($container->has($this->service)) {
            $service = $container->get($this->service);
        } else {
            throw new \RuntimeException(sprintf('Service "%s" not exists in container', $this->service));
        }

        if (method_exists($service, $this->method)) {
            call_user_func_array([$service, $this->method], $this->arguments);
        } else {
            throw new \RuntimeException(sprintf('Method "%s" not exists in service "%s"', $this->method, $this->service));
        }
    }

}