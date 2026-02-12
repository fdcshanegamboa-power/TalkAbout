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
        $user = $this->Authentication->getIdentity();
        $this->set(compact('user'));
    }
}
