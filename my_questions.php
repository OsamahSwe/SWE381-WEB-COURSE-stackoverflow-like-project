<?php
// my_questions.php
require __DIR__ . '/config.php';

// 1. Guard: only logged-in users
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/header.php';

// 2. Fetch this user’s questions
$stmt = $pdo->prepare("
    SELECT id, title, created_at
    FROM questions
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$myQuestions = $stmt->fetchAll();
?>

<h2>My Questions</h2>

<?php if (empty($myQuestions)): ?>
    <p>You haven’t asked any questions yet. <a href="ask_question.php">Ask one now</a>.</p>
<?php else: ?>
    <ul>
        <?php foreach ($myQuestions as $q): ?>
            <li>
                <a href="view_question.php?id=<?= $q['id'] ?>">
                    <?= htmlspecialchars($q['title']) ?>
                </a>
                <span style="color:#666;font-size:.9em;">
                    (asked <?= date('M j, Y', strtotime($q['created_at'])) ?>)
                </span>
                • <a href="edit_question.php?id=<?= $q['id'] ?>">Edit</a>
                | <a href="delete_question.php?id=<?= $q['id'] ?>"
                    onclick="return confirm('Delete this question and all its answers/comments?');">
                    Delete
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>