<?php
declare(strict_types=1);

namespace App\Controller;

class SearchController extends AppController
{
    public function index()
    {
        $this->request->allowMethod(['get']);
        
        $query = trim($this->request->getQuery('q', ''));
        
        if (strlen($query) < 2) {
            return $this->response->withType('application/json')->withStringBody(json_encode([
                'success' => true,
                'users' => [],
                'posts' => []
            ]));
        }
        
        $currentUserId = $this->request->getSession()->read('Auth.id');
        
        // Load tables
        $usersTable = $this->fetchTable('Users');
        $postsTable = $this->fetchTable('Posts');
        
        // Split query into words for flexible matching
        $words = preg_split('/\s+/', $query);
        $words = array_filter($words, function($word) {
            return strlen($word) >= 2;
        });
        
        // Build OR conditions for users (match any word)
        $userConditions = ['OR' => []];
        foreach ($words as $word) {
            $userConditions['OR'][] = ['username LIKE' => '%' . $word . '%'];
            $userConditions['OR'][] = ['full_name LIKE' => '%' . $word . '%'];
        }
        // Also search the full query
        $userConditions['OR'][] = ['username LIKE' => '%' . $query . '%'];
        $userConditions['OR'][] = ['full_name LIKE' => '%' . $query . '%'];
        
        // Search users
        $users = $usersTable->find()
            ->select(['id', 'username', 'full_name', 'profile_photo_path'])
            ->where([$userConditions, 'id !=' => $currentUserId])
            ->limit(5)
            ->toArray();
        
        $userResults = [];
        foreach ($users as $user) {
            $initial = !empty($user->full_name) 
                ? strtoupper(substr($user->full_name, 0, 1))
                : strtoupper(substr($user->username, 0, 1));
            
            $userResults[] = [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'profile_photo' => $user->profile_photo_path,
                'initial' => $initial
            ];
        }
        
        // Build OR conditions for posts (match any word)
        $postConditions = ['OR' => []];
        foreach ($words as $word) {
            $postConditions['OR'][] = ['Posts.content_text LIKE' => '%' . $word . '%'];
        }
        // Also search the full query
        $postConditions['OR'][] = ['Posts.content_text LIKE' => '%' . $query . '%'];
        
        // Search posts
        $posts = $postsTable->find()
            ->select(['Posts.id', 'Posts.content_text', 'Posts.created_at', 'Users.username', 'Users.full_name', 'Users.profile_photo_path'])
            ->contain(['Users' => function ($q) {
                return $q->select(['id', 'username', 'full_name', 'profile_photo_path']);
            }])
            ->where([
                $postConditions,
                'OR' => [
                    'Posts.visibility' => 'public',
                    'Posts.user_id' => $currentUserId
                ]
            ])
            ->order(['Posts.created_at' => 'DESC'])
            ->limit(5)
            ->toArray();
        
        $postResults = [];
        foreach ($posts as $post) {
            $author = $post->user->full_name ?: $post->user->username;
            $initial = !empty($post->user->full_name)
                ? strtoupper(substr($post->user->full_name, 0, 1))
                : strtoupper(substr($post->user->username, 0, 1));
            
            $timeAgo = $this->getTimeAgo($post->created_at);
            
            $postResults[] = [
                'id' => $post->id,
                'content' => $post->content_text,
                'author' => $author,
                'profile_photo' => $post->user->profile_photo_path,
                'initial' => $initial,
                'time' => $timeAgo
            ];
        }
        
        return $this->response->withType('application/json')->withStringBody(json_encode([
            'success' => true,
            'users' => $userResults,
            'posts' => $postResults
        ]));
    }
    
    private function getTimeAgo($datetime)
    {
        $now = new \DateTime();
        $ago = new \DateTime($datetime->format('Y-m-d H:i:s'));
        $diff = $now->diff($ago);
        
        if ($diff->y > 0) return $diff->y . 'y ago';
        if ($diff->m > 0) return $diff->m . 'mo ago';
        if ($diff->d > 0) return $diff->d . 'd ago';
        if ($diff->h > 0) return $diff->h . 'h ago';
        if ($diff->i > 0) return $diff->i . 'm ago';
        return 'Just now';
    }
}
