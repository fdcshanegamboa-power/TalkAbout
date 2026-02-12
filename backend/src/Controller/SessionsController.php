<?php
declare(strict_types=1);

namespace App\Controller;

class SessionsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        if (isset($this->Authentication)) {
            $this->Authentication->addUnauthenticatedActions(['login']);
        }
    }

    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        if ($this->request->is('post')) {
            $result = $this->request->getAttribute('authenticationResult') ?? $this->Authentication->getResult();

            if ($result && $result->isValid()) {
                $redirect = $this->Authentication->getLoginRedirect([
                    'controller' => 'Dashboard',
                    'action' => 'dashboard',
                ]);

                if (is_string($redirect)) {
                    return $this->redirect($redirect);
                }

                return $this->redirect(['controller' => 'Dashboard', 'action' => 'dashboard']);
            }

            $this->Flash->error('Invalid username or password');
        }
    }

    public function logout()
    {
        $result = $this->Authentication->getResult();

        if ($result && $result->isValid()) {
            $this->Authentication->logout();
            $this->getRequest()->getSession()->destroy();
            $this->Flash->success('You have been logged out.');
        }

        return $this->redirect(['controller' => 'Sessions', 'action' => 'login']);
    }
}
