<?php
/**
 * Created by PhpStorm.
 * User: user18
 * Date: 16.03.18
 * Time: 14:54
 */

namespace inisire\ReactBundle\EventDispatcher;


class ListenerPool extends \Volatile
{
    private $listeners = [];

    /**
     * @param string   $event
     * @param callable $listener
     */
    public function addListener($event, callable $listener)
    {
        $this->listeners[$event] = $listener;
    }

    /**
     * @param string $event
     *
     * @return array
     */
    public function getListeners($event)
    {
        return [$this->listeners[$event]] ?? [];
    }
}