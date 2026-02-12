<?php
declare(strict_types=1);

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->connect('/', ['controller' => 'Sessions', 'action' => 'login']);
        
        // Authentication & app routes
        $builder->connect('/login', ['controller' => 'Sessions', 'action' => 'login']);
        $builder->connect('/register', ['controller' => 'Users', 'action' => 'register']);
        $builder->connect('/logout', ['controller' => 'Sessions', 'action' => 'logout']);
        $builder->connect('/dashboard', ['controller' => 'Dashboard', 'action' => 'dashboard']);
        
        $builder->fallbacks();
    });
};
