<?php
declare(strict_types=1);

namespace App\Controller;

use Authentication\PasswordHasher\DefaultPasswordHasher;

class DashboardController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    public function dashboard()
    {
        return $this->redirect(['action' => 'home']);
    }

    public function home()
    {
        $usersTable = $this->getTableLocator()->get('Users');
        
        $id = $this->getAuthenticatedUserId();
        $user = null;
        
        if (!empty($id)) {
            try {
                $user = $usersTable->get($id);
            } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
                // User no longer exists, logout
                $this->Authentication->logout();
                $this->Flash->error('Your session has expired. Please login again.');
                return $this->redirect(['controller' => 'Sessions', 'action' => 'login']);
            }
        }
        
        $this->set(compact('user'));
    }

    public function settings()
    {
        $usersTable = $this->getTableLocator()->get('Users');

        $id = $this->getAuthenticatedUserId();

        if (empty($id)) {
            $this->Flash->error('User not found.');
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'home']);
        }

        try {
            $user = $usersTable->get($id);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->Authentication->logout();
            $this->Flash->error('Your session has expired. Please login again.');
            return $this->redirect(['controller' => 'Sessions', 'action' => 'login']);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $current = (string) $this->request->getData('current_password');
            $new = (string) $this->request->getData('new_password');
            $confirm = (string) $this->request->getData('confirm_password');

            $hasher = new DefaultPasswordHasher();
            $hash = $user->get('password_hash') ?? '';

            if (empty($current) || empty($new) || empty($confirm)) {
                $this->Flash->error('All password fields are required.');
            } elseif (!$hasher->check($current, $hash)) {
                $this->Flash->error('Current password is incorrect.');
            } elseif (strlen($new) < 8) {
                $this->Flash->error('New password must be at least 8 characters.');
            } elseif ($new !== $confirm) {
                $this->Flash->error('New password and confirmation do not match.');
            } else {
                $user->set('password', $new);
                if ($usersTable->save($user)) {
                    $this->Flash->success('Password changed successfully.');
                    return $this->redirect(['controller' => 'Profile', 'action' => 'profile']);
                }
                $this->Flash->error('Unable to change password. Please try again.');
            }
        }

        $this->set(compact('user'));
    }
}
