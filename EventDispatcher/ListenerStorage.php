<?php


namespace inisire\ReactBundle\EventDispatcher;

use inisire\ReactBundle\EventDispatcher\Listener\EventListener;

class ListenerStorage
{
    /**
     * @var iterable | EventListener[]
     */
    private $listeners;

    public function __construct(iterable $listeners)
    {
        $this->listeners = $listeners;
    }

    /**
     * @return EventListener[]|iterable
     */
    public function getListeners()
    {
        return $this->listeners;
    }
}