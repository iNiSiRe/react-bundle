<?php

namespace inisire\ReactBundle\Threaded;

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
        set_error_handler(function (int $errno , string $errstr, string $errfile, int $errline, array $errcontext = null) {

            $message = sprintf('Uncaught exception %s with message "%s" at %s:%s', $errno, $errstr, $errfile, $errline);
            echo $message . PHP_EOL;
            syslog(LOG_ERR, $message);

        }, E_ALL);

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

            echo $message . PHP_EOL;
        });

        register_shutdown_function(function () {});

        require_once($this->loader);

        $kernel = Kernel::create($this->kernelFactory);

        if ($kernel instanceof ThreadedKernelInterface) {
            $kernel->setThreadNumber($this->getThreadId());
        }

        $kernel->boot();

        $logger = $kernel->getContainer()->get('logger');

        set_exception_handler(function ($e) use ($logger) {

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

            $logger->critical($message);
        });

        $logger->debug('Worker started');
    }

    public function start(int $options = PTHREADS_INHERIT_ALL)
    {
        return parent::start(PTHREADS_INHERIT_NONE);
    }
}