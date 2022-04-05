<?php

namespace inisire\ReactBundle\Command;

use inisire\ReactBundle\Event\LoopRunEvent;
use inisire\ReactBundle\Server\HttpServer;
use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class HttpServerStartCommand extends Command
{
    /**
     * @var HttpServer
     */
    private $server;

    /**
     * @var LoopInterface 
     */
    private $loop;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param HttpServer               $server
     * @param LoopInterface            $loop
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(HttpServer $server, LoopInterface $loop, EventDispatcherInterface $dispatcher)
    {
        parent::__construct();
        $this->server = $server;
        $this->loop = $loop;
        $this->dispatcher = $dispatcher;
    }

    protected function configure()
    {
        $this->setName('react:server:start');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->server->start();

        $this->loop->addSignal(SIGINT, function ($signal) {
            $this->loop->stop();
        });

        $this->loop->addSignal(SIGTERM, function ($signal) {
            $this->loop->stop();
        });

        $this->dispatcher->dispatch(new LoopRunEvent($this->loop));

        $this->loop->run();

        return 0;
    }
}