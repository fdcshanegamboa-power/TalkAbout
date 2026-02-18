<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Friendship extends Entity
{
    protected array $_accessible = [
        'requester_id' => true,
        'addressee_id' => true,
        'status' => true,
        'created_at' => true,
        'updated_at' => true,
        'requester' => true,
        'addressee' => true,
    ];
}
