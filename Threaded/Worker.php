<?php


namespace inisire\ReactBundle\Threaded;


use inisire\ReactBundle\EventDispatcher\KernelFactoryInterface;

class Worker extends \Worker
{
    /**
     * @var string
     */
    private $loader;

    /**
     * @var KernelFactoryInterface
     */
    private $kernelFactory;

    /**
     * @param string                 $loader
     * @param KernelFactoryInterface $kernelFactory
     */
    public function __construct($loader, KernelFactoryInterface $kernelFactory)
    {
        $this->loader = $loader;
        $this->kernelFactory = $kernelFactory;
    }

    public function run()
    {
        set_error_handler(function (int $errno , string $errstr, string $errfile, int $errline, array $errcontext) {

            syslog(LOG_ERR, sprintf('Uncaught exception %s with message "%s" at %s:%s', $errno, $errstr, $errfile, $errline));

        }, E_ALL);

        register_shutdown_function(function () {});

        set_exception_handler(function ($e) {

            if ($e instanceof \Throwable) {
                $message = sprintf(
                    'Uncaught exception %s with message "%s" at %s:%s',
                    get_class($e),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                );
            } else {
                $message = 'Unknown error';
            }

            syslog(LOG_ERR, $message);
        });

        require_once($this->loader);

        $kernel = Kernel::create($this->kernelFactory);
        $kernel->boot();
    }

    public function start(int $options = PTHREADS_INHERIT_ALL)
    {
        return parent::start(PTHREADS_INHERIT_NONE);
    }
}