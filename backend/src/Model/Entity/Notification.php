<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Notification Entity
 * 
 * Represents a single notification in the system.
 * 
 * @property int $id
 * @property int $user_id The user receiving the notification
 * @property string $type Type of notification (friend_request, post_liked, etc.)
 * @property int|null $actor_id The user who triggered the notification
 * @property string|null $target_type Type of target (post, comment, user)
 * @property int|null $target_id ID of the target entity
 * @property string|null $message Optional custom message
 * @property bool $is_read Whether the notification has been read
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime|null $updated_at
 * 
 * @property \App\Model\Entity\User $user The recipient user
 * @property \App\Model\Entity\User $actor The user who triggered the notification
 */
class Notification extends Entity
{
    /**
     * Fields that can be mass assigned
     */
    protected array $_accessible = [
        'user_id' => true,
        'type' => true,
        'actor_id' => true,
        'target_type' => true,
        'target_id' => true,
        'message' => true,
        'is_read' => true,
        'created_at' => true,
        'updated_at' => true,
        'user' => true,
        'actor' => true,
    ];

    /**
     * Fields that should be cast to specific types
     */
    protected array $_casts = [
        'is_read' => 'boolean',
    ];
}
