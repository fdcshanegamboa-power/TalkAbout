<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;

class FlashHelper extends Helper
{
    protected array $helpers = ['Html'];

    public function render(string $key = 'flash', array $options = []): ?string
    {
        $session = $this->getView()->getRequest()->getSession();
        
        if (!$session->check("Flash.$key")) {
            return null;
        }

        $flash = $session->consume("Flash.$key");
        
        if (empty($flash)) {
            return null;
        }

        $out = '';
        foreach ($flash as $message) {
            $type = $message['type'] ?? 'info';
            $text = $message['message'];
            
            $class = match($type) {
                'success' => 'bg-green-100 border-green-400 text-green-700',
                'error' => 'bg-red-100 border-red-400 text-red-700',
                'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
                default => 'bg-blue-100 border-blue-400 text-blue-700',
            };
            
            $out .= sprintf(
                '<div class="border-l-4 p-4 mb-4 rounded %s" role="alert">
                    <p>%s</p>
                </div>',
                $class,
                h($text)
            );
        }

        return $out;
    }
}
