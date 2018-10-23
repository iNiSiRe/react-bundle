<?php

namespace inisire\ReactBundle\EventDispatcher\Listener;

abstract class EventListener
{
    /**
     * @return string
     */
    abstract public function getEvent();

    /**
     * @param mixed $event
     */
    abstract public function onEvent($event);
}