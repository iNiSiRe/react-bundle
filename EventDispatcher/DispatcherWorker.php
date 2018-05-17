<?php

namespace inisire\ReactBundle\EventDispatcher;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpKernel\Kernel;

class DispatcherWorker extends \Thread
{
    /**
     * @var \Threaded
     */
    public $firedEvents;

    /**
     * @var \Threaded
     */
    public $listeners;

    /**
     * @var string
     */
    private $kernelClass;

    /**
     * @var array
     */
    private $kernelParameters;

    /**
     * DispatcherWorker constructor.
     *
     * @param string $kernelClass
     * @param array $kernelParameters
     */
    public function __construct($kernelClass, $kernelParameters = [])
    {
        $this->kernelClass = $kernelClass;
        $this->firedEvents = new \Volatile();
        $this->listeners = new ListenerPool();
        $this->kernelParameters = $kernelParameters;
    }

    /**
     * @param string   $eventName
     * @param callable $listener
     */
    public function addListener($eventName, callable $listener)
    {
        $this->listeners->addListener($eventName, $listener);
    }

    /**
     * @param string $name
     * @param object $event
     */
    public function addFiredEvent($name, $event)
    {
        $this->firedEvents[] = [$name, $event];
    }

    /**
     * Run thread
     */
    public function run()
    {
        require_once __DIR__ . '/../../../autoload.php';

        /** @var Kernel $kernel */
        $kernel = new $this->kernelClass('dev', false);

        $kernel->boot();

        $logger = $kernel->getContainer()->get('logger');

        set_error_handler(function () {}, E_ALL);
        register_shutdown_function(function () {});

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

        // Set thread-oriented kernel parameters
        foreach ($this->kernelParameters as $name => $value) {
            $kernel->getContainer()->setParameter($name, $value);
        }

        $that = $this;

        while (true) {

            while ($that->firedEvents->count() == 0) {

                $logger->debug('DispatcherWorker::run wait');

                usleep(1000000);
            }

            $eventData = $that->firedEvents->pop();

            $logger->debug('DispatcherWorker::run dequeue', [$eventData, $that->firedEvents->count()]);
            $logger->debug('DispatcherWorker::run listeners', [$that->listeners]);


            $name = $eventData[0];
            $event = $eventData[1];

            $listeners = $that->listeners->getListeners($name);

            if (empty($listeners)) {
                continue;
            }

            list($listener, $method) = $listeners[0];

            if ($listener instanceof ContainerAwareInterface) {
                $listener->setContainer($kernel->getContainer());
            }

            call_user_func([$listener, $method], $event);
        }
    }

    /**
     * @param int $options
     */
    public function start(int $options = PTHREADS_INHERIT_NONE)
    {
        parent::start(PTHREADS_INHERIT_NONE);
    }
}