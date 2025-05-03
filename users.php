<?php
// users.php
require __DIR__ . '/config.php';   // session + $pdo
include __DIR__ . '/header.php';   // header + <main>
?>

<h2>All Users</h2>

<?php
// Fetch all users
$stmt = $pdo->query("
    SELECT id, username, created_at
    FROM users
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll();
?>

<?php if (empty($users)): ?>
    <p>No users found.</p>
<?php else: ?>
    <ul style="padding-left:0;">
        <?php foreach ($users as $u): ?>
            <li style="list-style:none; margin-bottom:8px;">
                <a href="profile.php?id=<?= $u['id'] ?>">
                    <?= htmlspecialchars($u['username']) ?>
                </a>
                <small style="color:#666;">
                    joined <?= date('M j, Y', strtotime($u['created_at'])) ?>
                </small>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>