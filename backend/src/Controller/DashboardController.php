<?php
declare(strict_types=1);

namespace App\Controller;

use Authentication\PasswordHasher\DefaultPasswordHasher;

class DashboardController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    public function dashboard()
    {
        return $this->redirect(['action' => 'home']);
    }

    public function home()
    {
        // Load full user entity so sidebar has access to profile_photo_path
        $usersTable = $this->getTableLocator()->get('Users');
        $identity = $this->Authentication->getIdentity();
        
        $user = null;
        if ($identity) {
            $id = null;
            if (method_exists($identity, 'getIdentifier')) {
                $id = $identity->getIdentifier();
            } elseif (method_exists($identity, 'get')) {
                $id = $identity->get('id');
            } elseif (isset($identity->id)) {
                $id = $identity->id;
            }
            
            if (!empty($id)) {
                try {
                    $user = $usersTable->get($id);
                } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
                    // User no longer exists, logout
                    $this->Authentication->logout();
                    $this->Flash->error('Your session has expired. Please login again.');
                    return $this->redirect(['controller' => 'Sessions', 'action' => 'login']);
                }
            }
        }
        
        $this->set(compact('user'));
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
            return $this->redirect(['action' => 'dashboard']);
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
        // Render the existing `edit.php` template (avoid Cake's underscored auto-lookup)
    }

    /**
     * API: Get all posts for the feed
     */
    public function getPosts()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

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
        
        // Get posts with user info and images, ordered by most recent first
        $posts = $postsTable->find()
            ->contain(['Users', 'PostImages'])
            ->where(['Posts.deleted_at IS' => null])
            ->order(['Posts.created_at' => 'DESC'])
            ->limit(50)
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

            $result[] = [
                'id' => $post->id,
                'author' => $authorName,
                'about' => $user->about ?? '',
                'initial' => $initial,
                'text' => $post->content_text ?? '',
                'images' => $images,
                'time' => $timeAgo,
                'likes' => $likeCount,
                'liked' => $userLiked,
            ];
        }

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'posts' => $result
        ]));
    }

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

            $result[] = [
                'id' => $post->id,
                'author' => $authorName,
                'about' => $user->about ?? '',
                'initial' => $initial,
                'text' => $post->content_text ?? '',
                'images' => $images,
                'time' => $timeAgo,
                'likes' => $likeCount,
                'liked' => $userLiked,
            ];
        }

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'posts' => $result,
            'count' => count($result)
        ]));
    }

    /**
     * API: Create a new post
     */
    public function createPost()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is('post')) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]));
        }

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
        $postImagesTable = $this->getTableLocator()->get('PostImages');
        $usersTable = $this->getTableLocator()->get('Users');
        
        $contentText = $this->request->getData('content_text');
        $imageFiles = $this->request->getData('images'); // Array of images

        // Validate at least one content type
        if (empty($contentText) && empty($imageFiles)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post must have text or images'
            ]));
        }

        // Create post first
        $post = $postsTable->newEmptyEntity();
        $post->user_id = $userId;
        $post->content_text = $contentText;

        if (!$postsTable->save($post)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to create post'
            ]));
        }

        $uploadedImages = [];
        
        // Handle multiple image uploads
        if (!empty($imageFiles) && is_array($imageFiles)) {
            // Create directory if it doesn't exist
            if (!is_dir(WWW_ROOT . 'img' . DS . 'posts')) {
                mkdir(WWW_ROOT . 'img' . DS . 'posts', 0755, true);
            }

            $displayOrder = 0;
            foreach ($imageFiles as $imageFile) {
                if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
                    $fileType = $imageFile->getClientMediaType();
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    
                    if (!in_array($fileType, $allowedTypes)) {
                        continue; // Skip invalid types
                    }

                    $fileSize = $imageFile->getSize();
                    if ($fileSize > 5 * 1024 * 1024) {
                        continue; // Skip files too large
                    }

                    $extension = pathinfo($imageFile->getClientFilename(), PATHINFO_EXTENSION);
                    $newFilename = uniqid('post_' . $post->id . '_') . '.' . $extension;
                    $targetPath = WWW_ROOT . 'img' . DS . 'posts' . DS . $newFilename;

                    try {
                        $imageFile->moveTo($targetPath);
                        
                        // Save to post_images table
                        $postImage = $postImagesTable->newEmptyEntity();
                        $postImage->post_id = $post->id;
                        $postImage->image_path = $newFilename;
                        $postImage->display_order = $displayOrder++;
                        $postImagesTable->save($postImage);
                        
                        $uploadedImages[] = '/img/posts/' . $newFilename;
                    } catch (\Exception $e) {
                        // Continue with other images if one fails
                        continue;
                    }
                }
            }
        }
        

        // Load user info for response
        $user = $usersTable->get($userId);
        $authorName = $user->full_name ?? $user->username ?? 'You';
        $initial = strtoupper(substr($authorName, 0, 1));

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'post' => [
                'id' => $post->id,
                'author' => $authorName,
                'about' => $user->about ?? '',
                'initial' => $initial,
                'text' => $post->content_text ?? '',
                'images' => $uploadedImages,
                'time' => 'Just now',
                'likes' => 0,
                'liked' => false,
            ]
        ]));
    }
    /**
     * API: Update a post
     */
    public function updatePost()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is(['post', 'put', 'patch'])) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]));
        }

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

        $postId = $this->request->getData('post_id');
        $contentText = $this->request->getData('content_text');

        if (empty($postId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post ID is required'
            ]));
        }

        $postsTable = $this->getTableLocator()->get('Posts');
        
        try {
            $post = $postsTable->get($postId);
            
            // Verify the post belongs to the current user
            if ($post->user_id != $userId) {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'You can only edit your own posts'
                ]));
            }

            $post->content_text = $contentText;
            
            if ($postsTable->save($post)) {
                return $this->response->withStringBody(json_encode([
                    'success' => true,
                    'post' => [
                        'id' => $post->id,
                        'text' => $post->content_text
                    ]
                ]));
            } else {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Failed to update post'
                ]));
            }
        } catch (\Exception $e) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post not found'
            ]));
        }
    }

    /**
     * API: Delete a post (soft delete)
     */
    public function deletePost()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is(['post', 'delete'])) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]));
        }

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

        $postId = $this->request->getData('post_id');

        if (empty($postId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post ID is required'
            ]));
        }

        $postsTable = $this->getTableLocator()->get('Posts');
        
        try {
            $post = $postsTable->get($postId);
            
            // Verify the post belongs to the current user
            if ($post->user_id != $userId) {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'You can only delete your own posts'
                ]));
            }

            // Soft delete - set deleted_at timestamp
            $post->deleted_at = new \DateTime();
            
            if ($postsTable->save($post)) {
                return $this->response->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Post deleted successfully'
                ]));
            } else {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Failed to delete post'
                ]));
            }
        } catch (\Exception $e) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post not found'
            ]));
        }
    }

    /**
     * API: Like a post
     */
    public function likePost()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is('post')) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]));
        }

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

        $postId = $this->request->getData('post_id');

        if (empty($postId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post ID is required'
            ]));
        }

        $likesTable = $this->getTableLocator()->get('Likes');
        
        // Check if already liked
        $existingLike = $likesTable->find()
            ->where([
                'user_id' => $userId,
                'target_type' => 'post',
                'target_id' => $postId
            ])
            ->first();

        if ($existingLike) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post already liked'
            ]));
        }

        // Create new like
        $like = $likesTable->newEmptyEntity();
        $like->user_id = $userId;
        $like->target_type = 'post';
        $like->target_id = $postId;

        if ($likesTable->save($like)) {
            // Get updated like count
            $likeCount = $likesTable->find()
                ->where([
                    'target_type' => 'post',
                    'target_id' => $postId
                ])
                ->count();

            return $this->response->withStringBody(json_encode([
                'success' => true,
                'likes' => $likeCount
            ]));
        } else {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to like post'
            ]));
        }
    }

    /**
     * API: Unlike a post
     */
    public function unlikePost()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is('post')) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]));
        }

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

        $postId = $this->request->getData('post_id');

        if (empty($postId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post ID is required'
            ]));
        }

        $likesTable = $this->getTableLocator()->get('Likes');
        
        // Find the like
        $like = $likesTable->find()
            ->where([
                'user_id' => $userId,
                'target_type' => 'post',
                'target_id' => $postId
            ])
            ->first();

        if (!$like) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Like not found'
            ]));
        }

        if ($likesTable->delete($like)) {
            // Get updated like count
            $likeCount = $likesTable->find()
                ->where([
                    'target_type' => 'post',
                    'target_id' => $postId
                ])
                ->count();

            return $this->response->withStringBody(json_encode([
                'success' => true,
                'likes' => $likeCount
            ]));
        } else {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to unlike post'
            ]));
        }
    }
    public function settings()
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
        $current = (string)$this->request->getData('current_password');
        $new = (string)$this->request->getData('new_password');
        $confirm = (string)$this->request->getData('confirm_password');

        $hasher = new DefaultPasswordHasher();
        $hash = $user->get('password_hash') ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            $this->Flash->error('All password fields are required.');
        } elseif (!$hasher->check($current, $hash)) {
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
            $this->Flash->error('Unable to change password. Please try again.');
        }
    }

    $this->set(compact('user'));
}
}
