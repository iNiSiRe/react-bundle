<?php

use App\Kernel;
use inisire\ReactBundle\Event\Events;
use inisire\ReactBundle\Event\LoopRunEvent;

$loader = require __DIR__ . '/../../../../vendor/autoload.php';

$environment = $_ENV['SYMFONY_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev';

$kernel = new Kernel($environment, false);
$kernel->boot();

$container = $kernel->getContainer();

$container->get('react.http.server')->start();
$loop = $container->get('react.loop');

$event = new LoopRunEvent($loop);
$container->get('event_dispatcher')->dispatch(Events::LOOP_RUN, $event);

$loop->addPeriodicTimer(5, function () {

    // Cleanup memory
    gc_collect_cycles();

});

echo 'Ready to accept connections.' . PHP_EOL;

$loop->run();
