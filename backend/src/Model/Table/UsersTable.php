<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('username');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created_at' => 'new',
                    'updated_at' => 'always',
                ]
            ]
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('full_name')
            ->maxLength('full_name', 150, 'Full name must be less than 150 characters')
            ->requirePresence('full_name', 'create')
            ->notEmptyString('full_name', 'Full name is required')
            ->add('full_name', 'validFormat', [
                'rule' => function ($value) {
                    return trim($value) !== '';
                },
                'message' => 'Full name cannot contain only whitespace'
            ]);

        $validator
            ->scalar('username')
            ->maxLength('username', 15, 'Username must be less than 15 characters')
            ->minLength('username', 3, 'Username must be at least 3 characters')
            ->requirePresence('username', 'create')
            ->notEmptyString('username', 'Username is required')
            ->add('username', 'alphaNumericUnderscore', [
                'rule' => function ($value) {
                    return (bool)preg_match('/^[a-zA-Z0-9_]+$/', $value);
                },
                'message' => 'Username can only contain letters, numbers, and underscores'
            ])
            ->add('username', 'noWhitespace', [
                'rule' => function ($value) {
                    return (bool)preg_match('/^\S+$/', $value);
                },
                'message' => 'Username cannot contain whitespace'
            ])
            ->add('username', 'unique', [
                'rule' => 'validateUnique', 
                'provider' => 'table',
                'message' => 'This username is already taken'
            ]);

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->notEmptyString('password', 'Password is required')
            ->minLength('password', 8, 'Password must be at least 8 characters')
            ->add('password', 'noWhitespace', [
                'rule' => function ($value) {
                    return (bool)preg_match('/^\S+$/', $value);
                },
                'message' => 'Password cannot contain whitespace'
            ])
            ->add('password', 'hasUppercase', [
                'rule' => function ($value) {
                    return (bool)preg_match('/[A-Z]/', $value);
                },
                'message' => 'Password must contain at least one uppercase letter'
            ])
            ->add('password', 'hasLowercase', [
                'rule' => function ($value) {
                    return (bool)preg_match('/[a-z]/', $value);
                },
                'message' => 'Password must contain at least one lowercase letter'
            ])
            ->add('password', 'hasNumber', [
                'rule' => function ($value) {
                    return (bool)preg_match('/[0-9]/', $value);
                },
                'message' => 'Password must contain at least one number'
            ]);

        $validator
            ->scalar('profile_photo_path')
            ->maxLength('profile_photo_path', 255)
            ->allowEmptyString('profile_photo_path');

        $validator
            ->scalar('about')
            ->maxLength('about', 500, 'About section must be less than 500 characters')
            ->allowEmptyString('about');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        // Username uniqueness is already validated in validationDefault()
        // No need to add it here to avoid duplicate error messages
        return $rules;
    }

    public function beforeSave($event, $entity, $options)
    {
        // Convert username to lowercase before saving
        if ($entity->has('username') && !empty($entity->username)) {
            $entity->username = strtolower($entity->username);
        }

        // Trim full name to remove leading/trailing whitespace
        if ($entity->has('full_name') && !empty($entity->full_name)) {
            $entity->full_name = trim($entity->full_name);
        }

        return true;
    }
}
