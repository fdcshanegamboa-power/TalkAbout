<?php
declare(strict_types=1);

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->connect('/', ['controller' => 'Sessions', 'action' => 'login']);
        
        // Authentication & app routes
        $builder->connect('/login', ['controller' => 'Sessions', 'action' => 'login']);
        $builder->connect('/register', ['controller' => 'Users', 'action' => 'register']);
        $builder->connect('/logout', ['controller' => 'Sessions', 'action' => 'logout']);
        $builder->connect('/dashboard', ['controller' => 'Dashboard', 'action' => 'dashboard']);
        $builder->connect('/home', ['controller' => 'Dashboard', 'action' => 'dashboard']);
        $builder->connect('/settings', ['controller' => 'Dashboard', 'action' => 'settings']);
        
        // API routes for posts (must come before profile routes to avoid conflict)
        $builder->connect('/api/posts/list', ['controller' => 'Posts', 'action' => 'getPosts']);
        $builder->connect('/api/posts/user', ['controller' => 'Profile', 'action' => 'getUserPosts']);
        $builder->connect('/api/posts/user/*', ['controller' => 'Profile', 'action' => 'getAnyUserPosts']);
        $builder->connect('/api/posts/create', ['controller' => 'Posts', 'action' => 'createPost']);
        $builder->connect('/api/posts/update', ['controller' => 'Posts', 'action' => 'updatePost']);
        $builder->connect('/api/posts/delete', ['controller' => 'Posts', 'action' => 'deletePost']);
        
        // API routes for profile
        $builder->connect('/api/profile/user/*', ['controller' => 'Profile', 'action' => 'getUserProfile']);
        
        // API routes for likes
        $builder->connect('/api/posts/like', ['controller' => 'Likes', 'action' => 'likePost']);
        $builder->connect('/api/posts/unlike', ['controller' => 'Likes', 'action' => 'unlikePost']);
        
        // API routes for comments
        $builder->connect('/api/comments/add', ['controller' => 'Comments', 'action' => 'addComment']);
        $builder->connect('/api/comments/list/*', ['controller' => 'Comments', 'action' => 'getComments']);
        $builder->connect('/api/comments/delete/:id', ['controller' => 'Comments', 'action' => 'deleteComment'], ['pass' => ['id']]);
        
        // API routes for notifications
        $builder->connect('/api/notifications', ['controller' => 'Notifications', 'action' => 'index']);
        $builder->connect('/api/notifications/unread', ['controller' => 'Notifications', 'action' => 'unread']);
        $builder->connect('/api/notifications/count', ['controller' => 'Notifications', 'action' => 'count']);
        $builder->connect('/api/notifications/mark-as-read/:id', ['controller' => 'Notifications', 'action' => 'markAsRead'], ['pass' => ['id']]);
        $builder->connect('/api/notifications/mark-all-as-read', ['controller' => 'Notifications', 'action' => 'markAllAsRead']);
        $builder->connect('/api/notifications/delete/:id', ['controller' => 'Notifications', 'action' => 'delete'], ['pass' => ['id']]);
        
        // Profile routes (specific routes first, then parameterized)
        $builder->connect('/profile', ['controller' => 'Profile', 'action' => 'profile']);
        $builder->connect('/profile/edit', ['controller' => 'Profile', 'action' => 'editProfile']);
        $builder->connect('/profile/*', ['controller' => 'Profile', 'action' => 'viewProfile']);
        
        $builder->fallbacks();
    });
};
