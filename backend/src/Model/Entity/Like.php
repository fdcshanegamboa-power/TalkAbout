<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Like extends Entity
{
    protected array $_accessible = [
        'user_id' => true,
        'target_type' => true,
        'target_id' => true,
        'created_at' => true,
        'user' => true,
    ];
}
