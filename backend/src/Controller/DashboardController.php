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
        $user = $this->Authentication->getIdentity();
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

        $user = $usersTable->get($id);
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

        $user = $usersTable->get($id);

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
}
