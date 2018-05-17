<?php

namespace inisire\ReactBundle\EventDispatcher;

interface ThreadedKernelInterface
{
    const MAIN_THREAD = 0;

    public function setThreadNumber(int $number);

    public function getThreadNumber();
}
