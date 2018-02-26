<?php

$loader = require __DIR__ . '/../../../../app/autoload.php';

require_once __DIR__ . '/../../../../app/AppKernel.php';

$environment = $_ENV['SYMFONY_ENV'] ?? 'dev';

$kernel = new AppKernel($environment, $environment == 'dev');
$kernel->boot();

$container = $kernel->getContainer();

$container->get('react.http.server')->start();
$container->get('react.loop')->run();
