<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\UsersTable;

class UsersController extends AppController
{
    protected UsersTable $Users;

    public function initialize(): void
    {
        parent::initialize();
        $this->Users = $this->fetchTable('Users');
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        if (isset($this->Authentication)) {
            $this->Authentication->addUnauthenticatedActions(['register', 'checkUsername']);
        }
    }

    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        if ($this->request->is('post')) {
            // Prefer the request attribute set by the middleware, fall back to the component result
            $result = $this->request->getAttribute('authenticationResult') ?? $this->Authentication->getResult();

            if ($result && $result->isValid()) {
                $redirect = $this->Authentication->getLoginRedirect([
                    'controller' => 'Users',
                    'action' => 'dashboard',
                ]);

                if (is_string($redirect)) {
                    return $this->redirect($redirect);
                }

                return $this->redirect(url: ['controller' => 'Users', 'action' => 'dashboard']);
            }

            $this->Flash->error('Invalid username or password');
        }
    }

    public function register()
    {
        // If user is already authenticated, redirect to dashboard
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'dashboard']);
        }

        $this->request->allowMethod(['get', 'post']);
        $user = $this->Users->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            
            if ($this->Users->save($user)) {
                    $this->Flash->success('Registration successful! You can now login.');
                return $this->redirect(['controller' => 'Sessions', 'action' => 'login']);
            }
            
                $this->Flash->error('Registration failed. Please check the form errors and try again.');
        }
        
        $this->set(compact('user'));
    }

    /**
     * Check if a username is available
     * Returns JSON response with availability status
     */
    public function checkUsername()
    {
        $this->request->allowMethod(['get']);
        
        $username = $this->request->getQuery('username');
        
        // Validate input exists
        if (empty($username)) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'available' => false,
                    'message' => 'Username is required'
                ]));
        }
        
        // Normalize username (lowercase, like in beforeSave)
        $username = strtolower(trim($username));
        
        // Validate username format (must match frontend+backend validation)
        if (strlen($username) < 3) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'available' => false,
                    'message' => 'Username must be at least 3 characters'
                ]));
        }
        
        if (strlen($username) > 50) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'available' => false,
                    'message' => 'Username must be less than 50 characters'
                ]));
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'available' => false,
                    'message' => 'Username can only contain letters, numbers, and underscores'
                ]));
        }
        
        // Check if username exists in database
        $exists = $this->Users->exists(['username' => $username]);
        
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'available' => !$exists,
                'message' => $exists ? 'This username is already taken' : 'Username is available'
            ]));
    }
}
