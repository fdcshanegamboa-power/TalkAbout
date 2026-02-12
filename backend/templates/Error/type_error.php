<?php
// Minimal type error template
$message = $message ?? 'A type error occurred';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Type Error</title>
</head>
<body>
  <h1>Type Error</h1>
  <p><?php echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
</body>
</html>
