<?php

require __DIR__.'/../vendor/autoload.php';

use App\AppKernel;
use App\Command\PasswordGeneratorCommand;
use App\Command\PasswordVerifierCommand;
use Symfony\Component\Console\Application;

$kernel = new AppKernel("dev", false);
$kernel->boot();
$app = new Application('myapp', '1.0 (stable)');
$app->add(new PasswordGeneratorCommand());
$app->add(new PasswordVerifierCommand());
try {
    $app->run();
} catch (Exception $e) {
    echo $e->getMessage();
}
