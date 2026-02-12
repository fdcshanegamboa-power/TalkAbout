<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Controller\Controller as BaseController;
use Cake\Event\EventInterface;

class AppController extends BaseController
{
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');
        // Allow login/register without identity by default
        $this->Authentication->addUnauthenticatedActions(['login', 'register']);
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        // Ensure login/register are available without authentication
        $this->Authentication->addUnauthenticatedActions(['login', 'register']);
    }
}
