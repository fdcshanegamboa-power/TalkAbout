<?php
declare(strict_types=1);

namespace App\Controller;

use Authentication\PasswordHasher\DefaultPasswordHasher;

class ProfileController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        // Allow API endpoints without authentication
        $this->Authentication->addUnauthenticatedActions(['getUserProfile', 'getAnyUserPosts']);
    }

    public function profile()
    {
        // Load full user entity from the database so all fields (e.g. `about`) are available
        $usersTable = $this->getTableLocator()->get('Users');

        $identity = $this->Authentication->getIdentity();
        $id = null;
        if ($identity) {
            if (method_exists($identity, 'getIdentifier')) {
                $id = $identity->getIdentifier();
            } elseif (method_exists($identity, 'get')) {
                $id = $identity->get('id');
            } elseif (isset($identity->id)) {
                $id = $identity->id;
            }
        }

        if (empty($id)) {
            $this->Flash->error('User not found.');
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'home']);
        }

        try {
            $user = $usersTable->get($id);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->Authentication->logout();
            $this->Flash->error('Your session has expired. Please login again.');
            return $this->redirect(['controller' => 'Sessions', 'action' => 'login']);
        }
        
        $this->set(compact('user'));
    }

    public function editProfile()
    {
        $usersTable = $this->getTableLocator()->get('Users');

        $identity = $this->Authentication->getIdentity();
        $id = null;
        if ($identity) {
            if (method_exists($identity, 'getIdentifier')) {
                $id = $identity->getIdentifier();
            } elseif (method_exists($identity, 'get')) {
                $id = $identity->get('id');
            } elseif (isset($identity->id)) {
                $id = $identity->id;
            }
        }

        if (empty($id)) {
            $this->Flash->error('User not found.');
            return $this->redirect(['action' => 'profile']);
        }

        try {
            $user = $usersTable->get($id);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->Authentication->logout();
            $this->Flash->error('Your session has expired. Please login again.');
            return $this->redirect(['controller' => 'Sessions', 'action' => 'login']);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            // If the password-change form was submitted
            if ($this->request->getData('current_password') !== null) {
                $current = (string)$this->request->getData('current_password');
                $new = (string)$this->request->getData('new_password');
                $confirm = (string)$this->request->getData('confirm_password');

                $hasher = new DefaultPasswordHasher();
                $hash = $user->get('password_hash') ?? '';

                if (!$hasher->check($current, $hash)) {
                    $this->Flash->error('Current password is incorrect.');
                } elseif (strlen($new) < 8) {
                    $this->Flash->error('New password must be at least 8 characters.');
                } elseif ($new !== $confirm) {
                    $this->Flash->error('New password and confirmation do not match.');
                } else {
                    $user->set('password', $new);
                    if ($usersTable->save($user)) {
                        $this->Flash->success('Password changed successfully.');
                        return $this->redirect(['action' => 'profile']);
                    }
                    $this->Flash->error('Unable to change password.');
                }
            } else {
                $allowed = ['full_name', 'username', 'about'];
                $usersTable->patchEntity($user, $this->request->getData(), ['fields' => $allowed]);
                
                // Handle profile picture upload
                $profilePicture = $this->request->getData('profile_picture');
                if ($profilePicture && $profilePicture->getError() === UPLOAD_ERR_OK) {
                    $filename = $profilePicture->getClientFilename();
                    $fileSize = $profilePicture->getSize();
                    $fileType = $profilePicture->getClientMediaType();
                    
                    // Validate file type
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($fileType, $allowedTypes)) {
                        $this->Flash->error('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.');
                    }
                    // Validate file size (5MB max)
                    elseif ($fileSize > 5 * 1024 * 1024) {
                        $this->Flash->error('File size must be less than 5MB.');
                    }
                    else {
                        // Generate unique filename
                        $extension = pathinfo($filename, PATHINFO_EXTENSION);
                        $newFilename = uniqid('profile_' . $id . '_') . '.' . $extension;
                        $targetPath = WWW_ROOT . 'img' . DS . 'profiles' . DS . $newFilename;
                        
                        // Move uploaded file
                        try {
                            $profilePicture->moveTo($targetPath);
                            
                            // Delete old profile picture if exists
                            $oldPicture = $user->get('profile_photo_path');
                            if ($oldPicture) {
                                $oldPath = WWW_ROOT . 'img' . DS . 'profiles' . DS . $oldPicture;
                                if (file_exists($oldPath)) {
                                    @unlink($oldPath);
                                }
                            }
                            
                            // Update user entity
                            $user->set('profile_photo_path', $newFilename);
                        } catch (\Exception $e) {
                            $this->Flash->error('Failed to upload profile picture.');
                        }
                    }
                }
                
                if ($usersTable->save($user)) {
                    $this->Flash->success('Profile updated.');
                    return $this->redirect(['action' => 'profile']);
                }
                $this->Flash->error('Unable to save your profile. Please correct errors and try again.');
            }
        }

        $this->set(compact('user'));
    }

    /**
     * API: Get all posts for the current user's profile
     */
    public function getUserPosts()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        $identity = $this->Authentication->getIdentity();
        $userId = null;
        if ($identity) {
            if (method_exists($identity, 'getIdentifier')) {
                $userId = $identity->getIdentifier();
            } elseif (method_exists($identity, 'get')) {
                $userId = $identity->get('id');
            } elseif (isset($identity->id)) {
                $userId = $identity->id;
            }
        }

        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $postsTable = $this->getTableLocator()->get('Posts');
        $likesTable = $this->getTableLocator()->get('Likes');
        $commentsTable = $this->getTableLocator()->get('Comments');
        
        // Get posts only for this user, with user info and images, ordered by most recent first
        $posts = $postsTable->find()
            ->contain(['Users', 'PostImages'])
            ->where([
                'Posts.deleted_at IS' => null,
                'Posts.user_id' => $userId
            ])
            ->order(['Posts.created_at' => 'DESC'])
            ->limit(100)
            ->all();

        $result = [];
        foreach ($posts as $post) {
            $user = $post->user;
            $authorName = $user->full_name ?? $user->username ?? 'Unknown';
            $initial = strtoupper(substr($authorName, 0, 1));
            
            // Calculate relative time
            $createdAt = $post->created_at;
            $now = new \DateTime();
            $diff = $now->diff($createdAt);
            
            if ($diff->days > 0) {
                $timeAgo = $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
            } elseif ($diff->h > 0) {
                $timeAgo = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
            } elseif ($diff->i > 0) {
                $timeAgo = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
            } else {
                $timeAgo = 'Just now';
            }

            // Collect all images
            $images = [];
            if (!empty($post->post_images)) {
                foreach ($post->post_images as $img) {
                    $images[] = '/img/posts/' . $img->image_path;
                }
            }

            // Get like count for this post
            $likeCount = $likesTable->find()
                ->where([
                    'target_type' => 'post',
                    'target_id' => $post->id
                ])
                ->count();

            // Check if current user liked this post
            $userLiked = $likesTable->exists([
                'user_id' => $userId,
                'target_type' => 'post',
                'target_id' => $post->id
            ]);

            // Get comment count for this post
            $commentCount = $commentsTable->find()
                ->where([
                    'post_id' => $post->id,
                    'deleted_at IS' => null
                ])
                ->count();

            $result[] = [
                'id' => $post->id,
                'user_id' => $user->id,
                'username' => $user->username ?? '',
                'author' => $authorName,
                'about' => $user->about ?? '',
                'initial' => $initial,
                'profile_photo' => $user->profile_photo_path ?? '',
                'text' => $post->content_text ?? '',
                'images' => $images,
                'time' => $timeAgo,
                'likes' => $likeCount,
                'liked' => $userLiked,
                'comments' => $commentCount,
            ];
        }

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'posts' => $result,
            'count' => count($result)
        ]));
    }

    /**
     * View any user's profile by username
     */
    public function viewProfile()
    {
        // Get username from passed parameters
        $passedArgs = $this->request->getParam('pass', []);
        $username = !empty($passedArgs) ? $passedArgs[0] : null;

        if (empty($username)) {
            $this->Flash->error('Invalid user.');
            return $this->redirect(['action' => 'profile']);
        }

        $usersTable = $this->getTableLocator()->get('Users');
        
        // Find user by username
        $user = $usersTable->find()
            ->where(['username' => $username])
            ->first();

        if (!$user) {
            $this->Flash->error('User not found.');
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'home']);
        }

        $userId = $user->id;

        // Get current logged-in user ID to check if viewing own profile
        $identity = $this->Authentication->getIdentity();
        $currentUserId = null;
        if ($identity) {
            if (method_exists($identity, 'getIdentifier')) {
                $currentUserId = $identity->getIdentifier();
            } elseif (method_exists($identity, 'get')) {
                $currentUserId = $identity->get('id');
            } elseif (isset($identity->id)) {
                $currentUserId = $identity->id;
            }
        }

        $isOwnProfile = ($currentUserId == $userId);
        
        $this->set(compact('user', 'currentUserId', 'isOwnProfile'));
    }

    /**
     * API: Get current logged-in user's profile data
     */
    public function getCurrentUserProfile()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        $identity = $this->Authentication->getIdentity();
        if (!$identity) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Not authenticated'
            ]));
        }

        $id = null;
        if (method_exists($identity, 'getIdentifier')) {
            $id = $identity->getIdentifier();
        } elseif (method_exists($identity, 'get')) {
            $id = $identity->get('id');
        } elseif (isset($identity->id)) {
            $id = $identity->id;
        }

        if (empty($id)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User ID not found'
            ]));
        }

        $usersTable = $this->getTableLocator()->get('Users');
        try {
            $user = $usersTable->get($id);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not found'
            ]));
        }

        $fullName = $user->get('full_name') ?? '';
        $username = $user->get('username') ?? '';
        $initial = strtoupper(substr($fullName ?: $username ?: 'U', 0, 1));

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'user' => [
                'id' => $user->get('id'),
                'username' => $username,
                'full_name' => $fullName,
                'about' => $user->get('about') ?? '',
                'profile_photo_path' => $user->get('profile_photo_path') ?? '',
                'initial' => $initial
            ]
        ]));
    }

    /**
     * API: Get user profile data by username
     */
    public function getUserProfile()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        // Get username from passed parameters
        $passedArgs = $this->request->getParam('pass', []);
        $username = !empty($passedArgs) ? $passedArgs[0] : null;

        if (empty($username)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Username is required'
            ]));
        }

        // Find user by username
        $usersTable = $this->getTableLocator()->get('Users');
        $user = $usersTable->find()
            ->where(['username' => $username])
            ->first();

        if (!$user) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not found'
            ]));
        }

        $initial = strtoupper(substr($user->full_name ?: $user->username ?: 'U', 0, 1));

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name ?? '',
                'about' => $user->about ?? '',
                'profile_photo_path' => $user->profile_photo_path ?? '',
                'initial' => $initial
            ]
        ]));
    }

    /**
     * API: Get posts for any user by username
     */
    public function getAnyUserPosts()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        // Get username from passed parameters
        $passedArgs = $this->request->getParam('pass', []);
        $username = !empty($passedArgs) ? $passedArgs[0] : null;

        if (empty($username)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Username is required'
            ]));
        }

        // Find user by username
        $usersTable = $this->getTableLocator()->get('Users');
        $targetUser = $usersTable->find()
            ->where(['username' => $username])
            ->first();

        if (!$targetUser) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not found'
            ]));
        }

        $userId = $targetUser->id;

        // Get current user ID
        $identity = $this->Authentication->getIdentity();
        $currentUserId = null;
        if ($identity) {
            if (method_exists($identity, 'getIdentifier')) {
                $currentUserId = $identity->getIdentifier();
            } elseif (method_exists($identity, 'get')) {
                $currentUserId = $identity->get('id');
            } elseif (isset($identity->id)) {
                $currentUserId = $identity->id;
            }
        }

        $postsTable = $this->getTableLocator()->get('Posts');
        $likesTable = $this->getTableLocator()->get('Likes');
        $commentsTable = $this->getTableLocator()->get('Comments');
        
        // Get posts for the specified user
        $posts = $postsTable->find()
            ->contain(['Users', 'PostImages'])
            ->where([
                'Posts.deleted_at IS' => null,
                'Posts.user_id' => $userId
            ])
            ->order(['Posts.created_at' => 'DESC'])
            ->limit(100)
            ->all();

        $result = [];
        foreach ($posts as $post) {
            $user = $post->user;
            $authorName = $user->full_name ?? $user->username ?? 'Unknown';
            $initial = strtoupper(substr($authorName, 0, 1));
            
            // Calculate relative time
            $createdAt = $post->created_at;
            $now = new \DateTime();
            $diff = $now->diff($createdAt);
            
            if ($diff->days > 0) {
                $timeAgo = $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
            } elseif ($diff->h > 0) {
                $timeAgo = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
            } elseif ($diff->i > 0) {
                $timeAgo = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
            } else {
                $timeAgo = 'Just now';
            }

            // Collect all images
            $images = [];
            if (!empty($post->post_images)) {
                foreach ($post->post_images as $img) {
                    $images[] = '/img/posts/' . $img->image_path;
                }
            }

            // Get like count for this post
            $likeCount = $likesTable->find()
                ->where([
                    'target_type' => 'post',
                    'target_id' => $post->id
                ])
                ->count();

            // Check if current user liked this post
            $userLiked = false;
            if ($currentUserId) {
                $userLiked = $likesTable->exists([
                    'user_id' => $currentUserId,
                    'target_type' => 'post',
                    'target_id' => $post->id
                ]);
            }

            // Get comment count for this post
            $commentCount = $commentsTable->find()
                ->where([
                    'post_id' => $post->id,
                    'deleted_at IS' => null
                ])
                ->count();

            $result[] = [
                'id' => $post->id,
                'user_id' => $user->id,
                'username' => $user->username ?? '',
                'author' => $authorName,
                'about' => $user->about ?? '',
                'initial' => $initial,
                'profile_photo' => $user->profile_photo_path ?? '',
                'text' => $post->content_text ?? '',
                'images' => $images,
                'time' => $timeAgo,
                'likes' => $likeCount,
                'liked' => $userLiked,
                'comments' => $commentCount,
            ];
        }

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'posts' => $result,
            'count' => count($result)
        ]));
    }
}
