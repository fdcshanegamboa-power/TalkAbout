<?php
/**
 * @var \Cake\Error\ErrorRenderer $error
 * @var \Cake\Http\Response $response
 * @var \Cake\Http\ServerRequest $request
 */
?>
<h1>Error</h1>
<p>An error occurred.</p>
<?php if (isset($error)): ?>
    <h2><?= h($error->getMessage()) ?></h2>
    <pre><?= h($error->getTraceAsString()) ?></pre>
<?php endif; ?>