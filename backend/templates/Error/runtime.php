<?php
// Minimal runtime error template
$message = $message ?? 'A runtime error occurred';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Runtime Error</title>
</head>
<body>
  <h1>Runtime Error</h1>
  <p><?php echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
</body>
</html>
