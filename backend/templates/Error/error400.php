<?php
/**
 * @var \Cake\Error\ErrorRenderer $error
 * @var \Cake\Http\Response $response
 * @var \Cake\Http\ServerRequest $request
 */
use Cake\Core\Configure;

// Helper function for escaping
if (!function_exists('h')) {
    function h($text) {
        return htmlspecialchars($text ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

// Get error code
$code = $this->response ? $this->response->getStatusCode() : 404;
$url = $this->request ? $this->request->getRequestTarget() : '';

// Get user-friendly messages based on error code
if ($code === 404) {
    $title = 'Page Not Found';
    $description = "The page you're looking for doesn't exist or has been moved.";
} elseif ($code === 403) {
    $title = 'Access Denied';
    $description = "You don't have permission to access this page.";
} elseif ($code === 401) {
    $title = 'Unauthorized';
    $description = 'You need to log in to access this page.';
} else {
    $title = 'Error ' . h($code);
    $description = 'An error occurred while processing your request.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($title) ?> - TalkAbout</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full">
        <div class="bg-white/95 backdrop-blur-lg rounded-3xl shadow-2xl border border-blue-200/50 p-8 text-center">
            
            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl shadow-lg flex items-center justify-center">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>

            <!-- Error Code -->
            <div class="mb-4">
                <h1 class="text-6xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">
                    <?= h($code) ?>
                </h1>
            </div>

            <!-- Title -->
            <h2 class="text-2xl font-bold text-gray-800 mb-3">
                <?= h($title) ?>
            </h2>

            <!-- Description -->
            <p class="text-gray-600 mb-6">
                <?= h($description) ?>
            </p>

            <?php if (Configure::read('debug') && isset($error)): ?>
            <!-- Debug Info (only in debug mode) -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                <p class="text-xs text-gray-500 font-mono break-all">
                    <strong>URL:</strong> <?= h($url) ?>
                </p>
                <p class="text-xs text-gray-500 font-mono mt-2">
                    <strong>Message:</strong> <?= h($error->getMessage()) ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="space-y-3">
                <a href="/dashboard" class="block w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-xl font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                    Go to Dashboard
                </a>
                <button onclick="history.back()" class="block w-full bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-300 transition-all duration-200">
                    Go Back
                </button>
            </div>

            <!-- Footer Link -->
            <div class="mt-6">
                <a href="/login" class="text-sm text-blue-600 hover:text-indigo-700 font-medium transition-colors duration-200">
                    Return to Login
                </a>
            </div>
        </div>

        <!-- App Name -->
        <div class="text-center mt-6">
            <h3 class="text-2xl font-extrabold tracking-tight text-blue-700">
                Talk<span class="text-indigo-600">About</span>
            </h3>
        </div>
    </div>
</body>
</html>
