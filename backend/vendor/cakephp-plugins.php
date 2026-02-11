<?php
$baseDir = dirname(dirname(__FILE__));

return [
    'plugins' => [
        'Authentication' => $baseDir . '/vendor/cakephp/authentication/',
        'Migrations' => $baseDir . '/vendor/cakephp/migrations/',
    ],
];
