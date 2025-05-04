<?php

require __DIR__.'/../vendor/autoload.php';

use App\AppKernel;
use App\SimpleCommand;
use Symfony\Component\Console\Application;

$kernel = new AppKernel("dev", false);
$kernel->boot();
$app = new Application('myapp', '1.0 (stable)');
$app->add(new SimpleCommand());
try {
    $app->run();
} catch (Exception $e) {
    echo $e->getMessage();
}
