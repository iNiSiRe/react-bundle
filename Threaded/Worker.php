<?php

namespace inisire\ReactBundle\Threaded;

use Symfony\Component\HttpKernel\KernelInterface;

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

    private function boot()
    {
        $GLOBALS['kernel'] = $this->kernelFactory->create();
        $GLOBALS['kernel']->boot();
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

        require_once($this->loader);

        $this->boot();

        $logger = $this->getKernel()->getContainer()->get('logger');

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

    /**
     * @return bool
     */
    public function shutdown()
    {
        if ($this->getKernel()) {
            $this->getKernel()->shutdown();
            $GLOBALS['kernel'] = null;
        }

        return parent::shutdown();
    }

    /**
     * @return KernelInterface
     */
    public function getKernel()
    {
        return $GLOBALS['kernel'];
    }
}