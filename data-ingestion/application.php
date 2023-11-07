<?php

/**
 * Application entry point. Intended to be used by CLI.
 * 
 * Usage: `php application.php <params>`
 * 
 * See `php application.php help` for usage.
 */

require __DIR__ . '/vendor/autoload.php';

use App\Command\AlgoliaImportCommand;
use Symfony\Component\Console\Application;

$app = new Application();

// The only purpose this application serves is to run this custom command.
$app->add(new AlgoliaImportCommand());

// Let's do it!
$app->run();
