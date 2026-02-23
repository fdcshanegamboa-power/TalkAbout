<?php
$this->assign('title', 'Post');
$this->Html->script('components/modal', ['block' => 'script']);
$this->Html->script('components/post_card', ['block' => 'script']);
$this->Html->script('components/left_sidebar', ['block' => 'script']);
$this->Html->script('components/right_sidebar', ['block' => 'script']);
$this->Html->script('posts/view', ['block' => 'script']);

$payload = [
    'id' => $post->id,
    'user_id' => $author->id ?? null,
    'username' => $author->username ?? '',
    'author' => $author->full_name ?? $author->username ?? '',
    'about' => $author->about ?? '',
    'initial' => strtoupper(substr($author->full_name ?? $author->username ?? 'U', 0, 1)),
    'profile_photo' => $author->profile_photo_path ? (preg_match('/^https?:\/\//', $author->profile_photo_path) ? $author->profile_photo_path : '/img/profiles/' . $author->profile_photo_path) : '',
    'text' => $post->content_text ?? '',
    'images' => $images ?? [],
    'time' => $post->created_at ? $post->created_at->format('M j, Y H:i') : '',
    'created_at' => $post->created_at ? $post->created_at->format(DATE_ATOM) : '',
    'likes' => (int)($likesCount ?? 0),
    'liked' => $userLiked ?? false,
    'visibility' => $post->visibility ?? 'public',
    'comments' => count($comments ?? []),
    'commentsList' => array_map(function($c) {
        return [
            'id' => $c['id'],
            'user_id' => isset($c['user_id']) ? $c['user_id'] : (isset($c['userId']) ? $c['userId'] : null),
            'author' => $c['author'],
            'profile_photo' => $c['profile_photo'],
            'initial' => $c['initial'],
            'content_text' => $c['content_text'],
            'content_image_path' => $c['content_image_path'],
            'created_at' => $c['created_at'] ? $c['created_at']->format(DATE_ATOM) : '',
            'time' => $c['created_at'] ? $c['created_at']->format(DATE_ATOM) : '',
            'likes' => isset($c['likes']) ? (int)$c['likes'] : 0,
            'liked' => !empty($c['liked']) ? (bool)$c['liked'] : false
        ];
    }, $comments ?? []),
    'showComments' => true,
    'showMenu' => false,
    'isEditing' => false,
    'editText' => $post->content_text ?? '',
    'editVisibility' => $post->visibility ?? 'public',
    'newCommentText' => '',
    'commentImageFile' => null,
    'commentImagePreview' => null,
    'loadingComments' => false,
    'isSubmittingComment' => false
];
?>

<?= $this->element('top_navbar') ?>

<div id="post-view-app" v-cloak class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100">
    <?= $this->element('mobile_header') ?>

    <div class="max-w-9xl mx-auto px-4 sm:px-6 pt-4 pb-20 md:pt-20 md:pb-6 lg:pb-6">
        <div class="md:flex md:gap-4 lg:gap-6">

            <?= $this->element('left_sidebar', ['active' => 'home']) ?>

            <main class="flex-1 space-y-4 lg:space-y-6 mt-4 md:mt-0">
                <!-- Back Button -->
                <button @click="$event.preventDefault(); window.history.back()"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 
                               bg-white/80 backdrop-blur rounded-xl shadow-sm border border-blue-100 
                               hover:bg-blue-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back
                </button>

                <?= $this->element('post_card', ['canEdit' => false]) ?>
            </main>

            <?= $this->element('right_sidebar') ?>
        </div>
    </div>

    <?= $this->element('mobile_nav', ['active' => 'home']) ?>
    
    <?= $this->element('modal') ?>
</div>

<script>
window.postViewData = <?= json_encode($payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>
