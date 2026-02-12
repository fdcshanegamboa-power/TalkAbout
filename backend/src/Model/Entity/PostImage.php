<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class PostImage extends Entity
{
    protected array $_accessible = [
        'post_id' => true,
        'image_path' => true,
        'display_order' => true,
        'created_at' => true,
        'post' => true,
    ];
}
