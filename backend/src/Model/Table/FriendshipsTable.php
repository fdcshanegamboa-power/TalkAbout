<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class FriendshipsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('friendships');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created_at' => 'new',
                    'updated_at' => 'always',
                ]
            ]
        ]);

        // Association with Users (requester)
        $this->belongsTo('Requester', [
            'className' => 'Users',
            'foreignKey' => 'requester_id',
            'joinType' => 'INNER',
        ]);

        // Association with Users (addressee)
        $this->belongsTo('Addressee', [
            'className' => 'Users',
            'foreignKey' => 'addressee_id',
            'joinType' => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->integer('requester_id')
            ->requirePresence('requester_id', 'create')
            ->notEmptyString('requester_id');

        $validator
            ->integer('addressee_id')
            ->requirePresence('addressee_id', 'create')
            ->notEmptyString('addressee_id');

        $validator
            ->scalar('status')
            ->inList('status', ['pending', 'accepted', 'rejected', 'blocked'])
            ->requirePresence('status', 'create')
            ->notEmptyString('status');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['requester_id'], 'Requester'), ['errorField' => 'requester_id']);
        $rules->add($rules->existsIn(['addressee_id'], 'Addressee'), ['errorField' => 'addressee_id']);

        // Custom rule: prevent self-friending
        $rules->add(function ($entity, $options) {
            if ($entity->requester_id === $entity->addressee_id) {
                return false;
            }
            return true;
        }, 'noSelfFriend', [
            'errorField' => 'addressee_id',
            'message' => 'You cannot send a friend request to yourself.'
        ]);

        return $rules;
    }

    /**
     * Check if a friendship exists between two users (in either direction)
     *
     * @param int $userId1 First user ID
     * @param int $userId2 Second user ID
     * @return \App\Model\Entity\Friendship|null
     */
    public function getFriendship(int $userId1, int $userId2)
    {
        return $this->find()
            ->where([
                'OR' => [
                    [
                        'requester_id' => $userId1,
                        'addressee_id' => $userId2,
                    ],
                    [
                        'requester_id' => $userId2,
                        'addressee_id' => $userId1,
                    ],
                ],
            ])
            ->first();
    }

    /**
     * Get all friends for a user (accepted friendships only)
     *
     * @param int $userId User ID
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function getFriends(int $userId): SelectQuery
    {
        return $this->find()
            ->where([
                'OR' => [
                    ['requester_id' => $userId],
                    ['addressee_id' => $userId],
                ],
                'status' => 'accepted',
            ])
            ->contain(['Requester', 'Addressee']);
    }

    /**
     * Get pending friend requests for a user
     *
     * @param int $userId User ID
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function getPendingRequests(int $userId): SelectQuery
    {
        return $this->find()
            ->where([
                'addressee_id' => $userId,
                'status' => 'pending',
            ])
            ->contain(['Requester']);
    }

    /**
     * Get sent friend requests for a user
     *
     * @param int $userId User ID
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function getSentRequests(int $userId): SelectQuery
    {
        return $this->find()
            ->where([
                'requester_id' => $userId,
                'status' => 'pending',
            ])
            ->contain(['Addressee']);
    }
}
