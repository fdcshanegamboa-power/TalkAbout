<?php
declare(strict_types=1);

namespace App\Controller;

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
        $user = $this->Authentication->getIdentity();
        $this->set(compact('user'));
    }

    public function profile()
    {
        $user = $this->Authentication->getIdentity();
        $this->set(compact('user'));
    }
}
