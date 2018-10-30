<?php

namespace inisire\ReactBundle\Threaded;

interface ThreadedKernelInterface
{
    const MAIN_THREAD = -1;

    public function setThreadNumber($number);

    public function getThreadNumber();
}
