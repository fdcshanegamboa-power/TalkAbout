<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Friendship Entity
 * 
 * @property int $id
 * @property int $requester_id
 * @property int $addressee_id
 * @property string $status
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime|null $updated_at
 * 
 * @property \App\Model\Entity\User $requester
 * @property \App\Model\Entity\User $addressee
 */
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
