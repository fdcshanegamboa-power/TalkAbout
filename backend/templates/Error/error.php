<?php
// Minimal error template
$message = $message ?? 'An error occurred';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Error</title>
</head>
<body>
  <h1>Error</h1>
  <p><?php echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
</body>
</html>
