<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;

class User extends Entity
{
    protected array $_accessible = [
        'full_name' => true,
        'username' => true,
        'password' => true,
        'profile_photo_path' => true,
        'created_at' => true,
        'updated_at' => true,
    ];

    protected array $_hidden = [
        'password_hash',
    ];

    // Intercept 'password' field and hash it into 'password_hash' column
    protected function _setPassword(string $password): ?string
    {
        if (strlen($password) > 0) {
            $this->set('password_hash', (new DefaultPasswordHasher())->hash($password));
        }
        return null;
    }
}
