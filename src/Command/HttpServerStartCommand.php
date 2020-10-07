<?php

namespace inisire\ReactBundle\Command;

use inisire\ReactBundle\Server\HttpServer;
use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param string|null   $name
     * @param HttpServer    $server
     * @param LoopInterface $loop
     */
    public function __construct(string $name = null, HttpServer $server, LoopInterface $loop)
    {
        parent::__construct($name);
        $this->server = $server;
        $this->loop = $loop;
    }

    protected function configure()
    {
        $this->setName('react:server:start');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->server->start();
        $this->loop->run();
    }
}