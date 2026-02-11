<?php
declare(strict_types=1);

// Load paths constants and Composer's autoloader
require dirname(__DIR__) . '/config/paths.php';
require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application;
use Cake\Http\Server;

// Bind your application to the server.
$server = new Server(new Application(dirname(__DIR__) . '/config'));

// Run the request/response through the application and emit the response.
$server->emit($server->run());
