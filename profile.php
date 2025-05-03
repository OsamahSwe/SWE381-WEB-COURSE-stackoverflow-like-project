<?php
// profile.php
require __DIR__ . '/config.php';

// 1. Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2. Fetch user details
$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found.";
    exit;
}

include __DIR__ . '/header.php';
?>

<article>
    <h2>Your Profile</h2>
    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Member since:</strong>
        <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
</article>

<?php include __DIR__ . '/footer.php'; ?>