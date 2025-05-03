<?php
// index.php — your custom Home
require __DIR__ . '/config.php';
include __DIR__ . '/header.php';
?>

<h2>
  Welcome<?= isset($_SESSION['username'])
            ? ", " . htmlspecialchars($_SESSION['username'])
            : ""
          ?>!
</h2>

<p>
  <?php if (isset($_SESSION['username'])): ?>
    Glad to have you back. Browse questions or ask one above.
  <?php else: ?>
    Please <a href="login.php">log in</a> or <a href="register.php">register</a> to participate.
  <?php endif; ?>
</p>

<?php include __DIR__ . '/footer.php'; ?>