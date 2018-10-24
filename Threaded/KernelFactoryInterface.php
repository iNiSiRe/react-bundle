<?php

namespace inisire\ReactBundle\Threaded;

use Symfony\Component\HttpKernel\KernelInterface;

interface KernelFactoryInterface
{
    /**
     * @return ThreadedKernelInterface|KernelInterface
     */
    public function create();
}
