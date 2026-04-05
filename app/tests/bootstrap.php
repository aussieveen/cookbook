<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    require dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

$kernel = new Kernel('test', true);
$kernel->boot();

$application = new Application($kernel);
$application->setAutoExit(false);
$output = new ConsoleOutput();

// Create DB if not exists
$application->run(
    new ArrayInput(['command' => 'doctrine:database:create', '--if-not-exists' => true]),
    $output
);

// Ensure schema is up to date
$application->run(
    new ArrayInput(['command' => 'doctrine:migrations:migrate', '--no-interaction' => true]),
    $output
);

$kernel->shutdown();
