<?php

namespace inisire\ReactBundle\EventDispatcher;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

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
     * DispatcherWorker constructor.
     *
     * @param string          $kernelClass
     */
    public function __construct($kernelClass)
    {
        $this->kernelClass = $kernelClass;
        $this->firedEvents = new \Volatile();
        $this->listeners = new ListenerPool();
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

            $this->logger->critical($message);

        });

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