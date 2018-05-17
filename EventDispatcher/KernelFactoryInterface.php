<?php

namespace inisire\ReactBundle\EventDispatcher;

use Symfony\Component\HttpKernel\KernelInterface;

interface KernelFactoryInterface
{
    /**
     * @return ThreadedKernelInterface|KernelInterface
     */
    public function create();
}
