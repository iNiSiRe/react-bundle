<?php

use App\Kernel;
use inisire\ReactBundle\Event\Events;
use inisire\ReactBundle\Event\LoopRunEvent;

$loader = require __DIR__ . '/../../../../vendor/autoload.php';

$environment = $_ENV['SYMFONY_ENV'] ?? 'dev';

$kernel = new Kernel($environment, $environment == 'dev');
$kernel->boot();

$container = $kernel->getContainer();

$container->get('react.http.server')->start();
$loop = $container->get('react.loop');

$event = new LoopRunEvent($loop);
$container->get('event_dispatcher')->dispatch(Events::LOOP_RUN, $event);

$loop->run();
