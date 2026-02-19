<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Post Entity
 * 
 * @property int $id
 * @property int $user_id
 * @property string|null $content_text
 * @property string $visibility
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime|null $updated_at
 * @property \Cake\I18n\DateTime|null $deleted_at
 * 
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\PostImage[] $post_images
 */
class Post extends Entity
{
    protected array $_accessible = [
        'user_id' => true,
        'content_text' => true,
        'visibility' => true,
        'created_at' => true,
        'updated_at' => true,
        'deleted_at' => true,
        'user' => true,
        'post_images' => true,
    ];
}
