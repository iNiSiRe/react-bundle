<?php

namespace inisire\ReactBundle\Event;

use React\EventLoop\LoopInterface;
use Symfony\Contracts\EventDispatcher\Event;

class LoopRunEvent extends Event
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * LoopStartEvent constructor.
     *
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @param LoopInterface $loop
     *
     * @return LoopRunEvent
     */
    public function setLoop($loop)
    {
        $this->loop = $loop;

        return $this;
    }
}
