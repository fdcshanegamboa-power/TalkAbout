<?php
declare(strict_types=1);

namespace App\View;

use Cake\View\View;

/**
 * AppView
 * 
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Cake\View\Helper\FormHelper $Form
 * @property \Cake\View\Helper\FlashHelper $Flash
 */
class AppView extends View
{
    public function initialize(): void
    {
        parent::initialize();
        
        $this->loadHelper('Html');
        $this->loadHelper('Form');
        $this->loadHelper('Flash');
    }
}
