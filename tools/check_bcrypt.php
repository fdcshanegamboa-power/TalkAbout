<?php
$password = 'iZE^Sb7?GszRGA';
$hash = '$2y$12$IbdThB5WNMTHVLkdolaMCu/19cKMpm0ujIGqRHv3QvjR5dby75Rum';
echo password_verify($password, $hash) ? "MATCH\n" : "NO MATCH\n";
