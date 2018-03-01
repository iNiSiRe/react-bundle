<?php

use App\Kernel;

$loader = require __DIR__ . '/../../../../vendor/autoload.php';

$environment = $_ENV['SYMFONY_ENV'] ?? 'dev';

$kernel = new Kernel($environment, $environment == 'dev');
$kernel->boot();

$container = $kernel->getContainer();

$container->get('react.http.server')->start();
$container->get('react.loop')->run();
