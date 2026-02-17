<?php
$this->assign('title', 'Post');
$this->Html->script('components/post_card', ['block' => 'script']);
$this->Html->script('posts/view', ['block' => 'script']);

$payload = [
    'id' => $post->id,
    'user_id' => $author->id ?? null,
    'username' => $author->username ?? '',
    'author' => $author->full_name ?? $author->username ?? '',
    'about' => $author->about ?? '',
    'initial' => strtoupper(substr($author->full_name ?? $author->username ?? 'U', 0, 1)),
    'profile_photo' => $author->profile_photo_path ?? '',
    'text' => $post->content_text ?? '',
    'images' => $images ?? [],
    'time' => $post->created_at ? $post->created_at->format('M j, Y H:i') : '',
    'created_at' => $post->created_at ? $post->created_at->format(DATE_ATOM) : '',
    'likes' => (int)($likesCount ?? 0),
    'liked' => $userLiked ?? false,
    'comments' => count($comments ?? []),
    'commentsList' => array_map(function($c) {
        return [
            'id' => $c['id'],
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
    'newCommentText' => '',
    'commentImageFile' => null,
    'commentImagePreview' => null,
    'loadingComments' => false,
    'isSubmittingComment' => false
];
?>

<style>
    [v-cloak] {
        display: none;
    }
</style>

<?= $this->element('top_navbar') ?>

<div id="post-view-app" v-cloak class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100">
    <?= $this->element('mobile_header') ?>

    <div class="max-w-9xl mx-auto px-4 sm:px-6 pt-4 pb-20 md:pt-20 md:pb-6 lg:pb-6">
        <div class="md:flex md:gap-4 lg:gap-6">

            <?= $this->element('left_sidebar', ['active' => 'home']) ?>

            <main class="flex-1 space-y-4 lg:space-y-6 mt-4 md:mt-0">
                <div class="max-w-3xl mx-auto">
                    <?= $this->element('post_card', ['canEdit' => false]) ?>
                </div>
            </main>

            <?= $this->element('right_sidebar') ?>
        </div>
    </div>

    <?= $this->element('mobile_nav', ['active' => 'home']) ?>
</div>

<script>
window.postViewData = <?= json_encode($payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>
