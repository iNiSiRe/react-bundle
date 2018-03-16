<?php

namespace inisire\ReactBundle\EventDispatcher;

use Psr\Log\LoggerInterface;

class DispatcherWorker extends \Thread
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Threaded
     */
    public $firedEvents;

    /**
     * @var \Threaded
     */
    public $listeners;

    /**
     * DispatcherWorker constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->firedEvents = new \Volatile();
        $this->listeners = new ListenerPool();

        $this->start();
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

        $that = $this;

        while (true) {

            while ($that->firedEvents->count() == 0) {
                $that->logger->debug('DispatcherWorker::run wait');
                usleep(1000000);
            }

            $eventData = $that->firedEvents->pop();

            $that->logger->debug('DispatcherWorker::run dequeue', [$eventData, $that->firedEvents->count()]);
            $that->logger->debug('DispatcherWorker::run listeners', [$that->listeners]);

            $name = $eventData[0];
            $event = $eventData[1];

            $listeners = $this->listeners->getListeners($name);

            if (empty($listeners)) {
                continue;
            }

            list($listener, $method) = $listeners[0];

            call_user_func([$listener, $method], $event);
        }
    }

    public function start(int $options = PTHREADS_INHERIT_NONE)
    {
        parent::start(PTHREADS_INHERIT_NONE);
    }
}