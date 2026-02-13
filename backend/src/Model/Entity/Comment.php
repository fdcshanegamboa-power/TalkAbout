<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Comment Entity
 * 
 * @property int $id
 * @property int $post_id
 * @property int $user_id
 * @property string|null $content_text
 * @property string|null $content_image_path
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime|null $updated_at
 * @property \Cake\I18n\DateTime|null $deleted_at
 * 
 * @property \App\Model\Entity\Post $post
 * @property \App\Model\Entity\User $user
 */
class Comment extends Entity
{
    protected array $_accessible = [
        'post_id' => true,
        'user_id' => true,
        'content_text' => true,
        'content_image_path' => true,
        'created_at' => true,
        'updated_at' => true,
        'deleted_at' => true,
        'post' => true,
        'user' => true,
    ];
}
