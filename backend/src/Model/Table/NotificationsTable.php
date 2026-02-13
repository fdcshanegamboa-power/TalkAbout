<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Notifications Model
 * 
 * Manages notification data and business logic.
 */
class NotificationsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config Configuration array
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('notifications');
        $this->setPrimaryKey('id');

        // Timestamp behavior for created_at and updated_at
        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created_at' => 'new',
                    'updated_at' => 'always',
                ]
            ]
        ]);

        // Association with Users (recipient)
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);

        // Association with Actor (the user who triggered the notification)
        $this->belongsTo('Actors', [
            'className' => 'Users',
            'foreignKey' => 'actor_id',
        ]);
    }

    /**
     * Default validation rules
     *
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->integer('user_id')
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->scalar('type')
            ->inList('type', ['friend_request', 'post_liked', 'post_commented', 'comment_liked', 'mention'])
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        $validator
            ->integer('actor_id')
            ->allowEmptyString('actor_id');

        $validator
            ->scalar('target_type')
            ->inList('target_type', ['post', 'comment', 'user'])
            ->allowEmptyString('target_type');

        $validator
            ->integer('target_id')
            ->allowEmptyString('target_id');

        $validator
            ->scalar('message')
            ->allowEmptyString('message');

        $validator
            ->boolean('is_read')
            ->notEmptyString('is_read');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        // Ensure user_id exists in users table
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        
        // Ensure actor_id exists in users table (when provided)
        $rules->add($rules->existsIn(['actor_id'], 'Actors'), ['errorField' => 'actor_id']);

        return $rules;
    }

    /**
     * Find unread notifications for a specific user
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query object
     * @param array $options Options array containing 'user_id'
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findUnread(SelectQuery $query, array $options): SelectQuery
    {
        return $query
            ->where([
                'Notifications.user_id' => $options['user_id'],
                'Notifications.is_read' => false
            ])
            ->contain(['Actors'])
            ->order(['Notifications.created_at' => 'DESC']);
    }

    /**
     * Find all notifications for a specific user (read and unread)
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query object
     * @param array $options Options array containing 'user_id'
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findByUser(SelectQuery $query, array $options): SelectQuery
    {
        return $query
            ->where(['Notifications.user_id' => $options['user_id']])
            ->contain(['Actors'])
            ->order(['Notifications.created_at' => 'DESC']);
    }

    /**
     * Mark a notification as read
     *
     * @param int $notificationId The notification ID
     * @param int $userId The user ID (for security check)
     * @return bool
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = $this->find()
            ->where([
                'id' => $notificationId,
                'user_id' => $userId
            ])
            ->first();

        if (!$notification) {
            return false;
        }

        $notification->is_read = true;
        return (bool)$this->save($notification);
    }

    /**
     * Mark all notifications as read for a user
     *
     * @param int $userId The user ID
     * @return int Number of notifications marked as read
     */
    public function markAllAsRead(int $userId): int
    {
        return $this->updateAll(
            ['is_read' => true, 'updated_at' => new \DateTime()],
            ['user_id' => $userId, 'is_read' => false]
        );
    }

    /**
     * Get unread notification count for a user
     *
     * @param int $userId The user ID
     * @return int
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->find()
            ->where([
                'user_id' => $userId,
                'is_read' => false
            ])
            ->count();
    }

    /**
     * Create a notification (helper method)
     * This will be used later to create notifications from various actions
     *
     * @param array $data Notification data
     * @return \App\Model\Entity\Notification|false
     */
    public function createNotification(array $data)
    {
        $notification = $this->newEntity($data);
        return $this->save($notification);
    }
}
