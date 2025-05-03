<?php
// questions.php
require __DIR__ . '/config.php';
include __DIR__ . '/header.php';

// Fetch all questions + authors
$stmt = $pdo->prepare("
    SELECT q.id, q.title, q.created_at, u.username
    FROM questions q
    JOIN users u ON q.user_id = u.id
    ORDER BY q.created_at DESC
");
$stmt->execute();
$questions = $stmt->fetchAll();
?>

<h2>All Questions</h2>
<?php if (empty($questions)): ?>
    <p>No questions have been asked yet.</p>
<?php else: ?>
    <ul class="questions-list">
        <?php foreach ($questions as $q): ?>
            <li class="question-summary">
                <div class="question-content">
                    <h3 class="question-title">
                        <a href="view_question.php?id=<?= $q['id'] ?>">
                            <?= htmlspecialchars($q['title']) ?>
                        </a>
                    </h3>
                    <div class="user-info">
                        Asked by <?= htmlspecialchars($q['username']) ?>
                        on <?= date('M j, Y', strtotime($q['created_at'])) ?>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>