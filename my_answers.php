<?php
// my_answers.php
require __DIR__ . '/config.php';   // session + $pdo
include __DIR__ . '/header.php';   // opens <main>

// protect page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// fetch all answers by this user, along with question titles
$stmt = $pdo->prepare("
    SELECT a.id AS answer_id,
           a.content,
           a.created_at,
           q.id AS question_id,
           q.title
    FROM answers a
    JOIN questions q ON a.question_id = q.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$answers = $stmt->fetchAll();
?>

<h2>My Answers</h2>

<?php if (empty($answers)): ?>
    <p>You haven’t posted any answers yet.
        <a href="questions.php">Browse questions</a>.
    </p>
<?php else: ?>
    <ul class="questions-list">
        <?php foreach ($answers as $a): ?>
            <li class="question-summary">
                <div class="question-content">
                    <h3 class="question-title">
                        <a href="view_question.php?id=<?= $a['question_id'] ?>">
                            <?= htmlspecialchars($a['title']) ?>
                        </a>
                    </h3>
                    <p class="answer-excerpt">
                        <?= nl2br(htmlspecialchars(substr($a['content'], 0, 200))) ?>
                        <?= strlen($a['content']) > 200 ? '…' : '' ?>
                    </p>
                    <p class="user-info" style="font-size:.9em;color:#666;">
                        answered on <?= date('M j, Y \a\t H:i', strtotime($a['created_at'])) ?>
                    </p>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>