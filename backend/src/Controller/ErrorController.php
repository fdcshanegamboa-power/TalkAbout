<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;

/**
 * Error Handling Controller
 * 
 * Controller used by ErrorHandler to render error responses
 * Extends base Controller to avoid authentication requirements from AppController
 */
class ErrorController extends Controller
{
    /**
     * Initialization hook method.
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
    }

    /**
     * beforeRender callback.
     *
     * @param \Cake\Event\EventInterface $event Event.
     * @return void
     */
    public function beforeRender(EventInterface $event)
    {
        parent::beforeRender($event);
        
        $this->viewBuilder()->setTemplatePath('Error');
        
        // Use AppView for proper rendering
        $this->viewBuilder()->setClassName('App\View\AppView');
    }
}
