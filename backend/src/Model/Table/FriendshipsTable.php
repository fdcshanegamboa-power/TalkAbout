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

    /**
     * Get friend suggestions for a user based on mutual friends and activity
     *
     * Algorithm weights:
     * - Mutual friends: Primary factor (friends of friends)
     * - Number of mutual friends: Higher score for more mutual connections
     * - Recent activity: Active users get slight boost
     *
     * Excludes:
     * - Current friends
     * - Pending/sent friend requests
     * - Blocked users
     * - Self
     *
     * @param int $userId User ID
     * @param int $limit Maximum number of suggestions to return (default 10)
     * @return array Array of suggested users with mutual friend counts
     */
    public function getSuggestions(int $userId, int $limit = 10): array
    {
        $connection = $this->getConnection();

        // Get IDs of users to exclude (already connected in some way)
        $excludeIds = [$userId]; // Start with self

        // Get all existing relationships (friends, pending, sent, blocked)
        $existingRelationships = $this->find()
            ->where([
                'OR' => [
                    ['requester_id' => $userId],
                    ['addressee_id' => $userId],
                ],
            ])
            ->select(['requester_id', 'addressee_id'])
            ->toArray();

        foreach ($existingRelationships as $rel) {
            $otherId = ($rel->requester_id == $userId) ? $rel->addressee_id : $rel->requester_id;
            if (!in_array($otherId, $excludeIds)) {
                $excludeIds[] = $otherId;
            }
        }

        $excludePlaceholders = implode(',', array_fill(0, count($excludeIds), '?'));

        // Build SQL query to find friends of friends with mutual friend count
        // Note: LIMIT uses direct integer interpolation (safe since $limit is type-hinted as int)
        $sql = "
            SELECT 
                u.id,
                u.username,
                u.full_name,
                u.profile_photo_path,
                COUNT(DISTINCT f1.id) as mutual_friends,
                MAX(u.created_at) as joined_at
            FROM users u
            INNER JOIN friendships f2 ON (
                (f2.requester_id = u.id OR f2.addressee_id = u.id)
                AND f2.status = 'accepted'
            )
            INNER JOIN friendships f1 ON (
                (
                    (f1.requester_id = ? AND f1.addressee_id = IF(f2.requester_id = u.id, f2.addressee_id, f2.requester_id))
                    OR
                    (f1.addressee_id = ? AND f1.requester_id = IF(f2.requester_id = u.id, f2.addressee_id, f2.requester_id))
                )
                AND f1.status = 'accepted'
            )
            WHERE u.id NOT IN ($excludePlaceholders)
            GROUP BY u.id, u.username, u.full_name, u.profile_photo_path
            ORDER BY mutual_friends DESC, joined_at DESC
            LIMIT " . (int)$limit . "
        ";

        // Execute query with parameters (excluding limit from params)
        $params = array_merge(
            [$userId, $userId],
            $excludeIds
        );

        $statement = $connection->execute($sql, $params);
        $results = $statement->fetchAll('assoc');

        // If we don't have enough mutual friend suggestions, add some popular/active users
        if (count($results) < $limit) {
            $remaining = $limit - count($results);
            $existingUserIds = array_merge($excludeIds, array_column($results, 'id'));
            $fallbackPlaceholders = implode(',', array_fill(0, count($existingUserIds), '?'));
            
            $fallbackSql = "
                SELECT 
                    u.id,
                    u.username,
                    u.full_name,
                    u.profile_photo_path,
                    0 as mutual_friends,
                    u.created_at as joined_at
                FROM users u
                WHERE u.id NOT IN ($fallbackPlaceholders)
                ORDER BY u.created_at DESC
                LIMIT " . (int)$remaining . "
            ";
            
            $fallbackStmt = $connection->execute($fallbackSql, $existingUserIds);
            $fallbackResults = $fallbackStmt->fetchAll('assoc');
            
            $results = array_merge($results, $fallbackResults);
        }

        return $results;
    }
}
