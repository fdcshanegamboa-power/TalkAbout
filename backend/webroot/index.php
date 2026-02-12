<?php
declare(strict_types=1);

// Load paths constants and Composer's autoloader
require dirname(__DIR__) . '/config/paths.php';
// Suppress deprecation output from early-loaded libraries to avoid headers-sent issues in dev.
@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_USER_DEPRECATED);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application;
use Cake\Http\Server;

// Bind your application to the server.
$server = new Server(new Application(dirname(__DIR__) . '/config'));

// Run the request/response through the application and emit the response.
$server->emit($server->run());
