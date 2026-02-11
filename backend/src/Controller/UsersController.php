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
        // Allow unauthenticated access to login and register
        $this->Authentication->addUnauthenticatedActions(['login', 'register']);
    }

    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();
        
        // If user is already authenticated, redirect
        if ($result && $result->isValid()) {
            $redirect = $this->request->getQuery('redirect', [
                'controller' => 'Users',
                'action' => 'dashboard',
            ]);
            return $this->redirect($redirect);
        }
        
        // Display authentication errors
        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error(__('Invalid username or password'));
        }
    }

    public function register()
    {
        $this->request->allowMethod(['get', 'post']);
        $user = $this->Users->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Registration successful! You can now login.'));
                return $this->redirect(['action' => 'login']);
            }
            
            $this->Flash->error(__('Registration failed. Please check the form errors and try again.'));
        }
        
        $this->set(compact('user'));
    }

    public function logout()
    {
        $result = $this->Authentication->getResult();
        
        if ($result && $result->isValid()) {
            $this->Authentication->logout();
            $this->Flash->success(__('You have been logged out.'));
        }
        
        return $this->redirect(['action' => 'login']);
    }

    public function dashboard()
    {
        $user = $this->Authentication->getIdentity();
        $this->set(compact('user'));
    }
}
