<?php


namespace inisire\ReactBundle\Threaded;


use inisire\ReactBundle\EventDispatcher\KernelFactoryInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class Kernel
{
    /**
     * @var KernelInterface
     */
    private static $instance;

    /**
     * @var bool
     */
    private static $loaded = false;

    /**
     * Singletone
     */
    private function __construct() {}

    /**
     * @param KernelFactoryInterface $factory
     *
     * @return KernelInterface
     */
    public static function create (KernelFactoryInterface $factory)
    {
        self::$instance = $factory->create();
        self::$loaded = true;

        return self::$instance;
    }

    /**
     * @return KernelInterface
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * @return bool
     */
    public static function isLoaded()
    {
        return self::$loaded;
    }
}