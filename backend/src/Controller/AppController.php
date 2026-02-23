<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Controller\Controller as BaseController;
use Cake\Event\EventInterface;

/**
 * Application Controller
 * 
 * @property \Cake\Controller\Component\FlashComponent $Flash
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 * @property \Cake\Http\ServerRequest $request
 * @property \Cake\Http\Response $response
 */
class AppController extends BaseController
{
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');
        $this->Authentication->addUnauthenticatedActions(['login', 'register', 'checkUsername']);
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['login', 'register', 'checkUsername']);
    }

    /**
     * Helper method to get authenticated user ID
     * Centralizes the logic for extracting user ID from authentication identity
     * 
     * @return int|null The authenticated user's ID, or null if not authenticated
     */
    protected function getAuthenticatedUserId(): ?int
    {
        $identity = $this->Authentication->getIdentity();
        
        if (!$identity) {
            return null;
        }

        if (method_exists($identity, 'getIdentifier')) {
            return $identity->getIdentifier();
        } elseif (method_exists($identity, 'get')) {
            return $identity->get('id');
        } elseif (isset($identity->id)) {
            return $identity->id;
        }

        return null;
    }
}
