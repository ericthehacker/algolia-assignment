<?php

require __DIR__ . '/vendor/autoload.php';

use App\Command\AlgoliaImportCommand;
use Symfony\Component\Console\Application;

$app = new Application();

$app->add(new AlgoliaImportCommand());

$app->run();
