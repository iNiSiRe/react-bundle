<?php

namespace inisire\ReactBundle\EventDispatcher;

use inisire\ReactBundle\EventDispatcher\Listener\EventListener;

class ListenerPool extends \Volatile
{
    /**
     * @var array
     */
    private $listeners;

    public function __construct()
    {
        $this->listeners = [];
    }

    /**
     * @param string   $event
     * @param callable $listener
     */
    public function addListener($event, callable $listener)
    {
        var_dump($event);

        $this->listeners[$event] = $listener;

        var_dump(array_keys($this->listeners));
    }

    /**
     * @param string $event
     *
     * @return array
     */
    public function getListeners($event)
    {
        var_dump(array_keys($this->listeners));

        return isset($this->listeners[$event]) ? $this->listeners[$event] : [];
    }

    /**
     * @param iterable|EventListener[] $listeners
     *
     * @throws \Exception
     */
    public function addListeners($listeners)
    {
        foreach ($listeners as $listener) {
            $this->addListener($listener->getEvent(), [$listener, 'onEvent']);
        }
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->listeners;
    }
}