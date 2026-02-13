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
            $this->Authentication->addUnauthenticatedActions(['register']);
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

                return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
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
}
