<?php
declare(strict_types=1);

use Cake\Core\Configure;

/*
 * Additional bootstrapping and configuration for CLI environments
 * should be put here.
 */

// Set the fullBaseUrl to allow URL generation in shell commands.
// This is useful when sending notification emails from shell commands.
Configure::write('App.fullBaseUrl', 'http://localhost');
