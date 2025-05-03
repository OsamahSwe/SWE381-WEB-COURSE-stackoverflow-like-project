<?php
// tags.php
require __DIR__ . '/config.php';  // session + $pdo
include __DIR__ . '/header.php';  // header + <main>

$tag = trim($_GET['tag'] ?? '');
?>

<h2>
    <?php if ($tag): ?>
        Questions tagged “<?= htmlspecialchars($tag) ?>”
    <?php else: ?>
        All Tags
    <?php endif; ?>
</h2>

<?php if ($tag): ?>
    <?php
    // Fetch questions for this tag
    $stmt = $pdo->prepare("
        SELECT q.id, q.title, q.description, u.username, q.created_at
        FROM question_tags qt
        JOIN questions q ON qt.question_id = q.id
        JOIN users u ON q.user_id = u.id
        WHERE qt.tag = ?
        ORDER BY q.created_at DESC
    ");
    $stmt->execute([$tag]);
    $questions = $stmt->fetchAll();
    ?>
    <p><?= count($questions) ?> result<?= count($questions) !== 1 ? 's' : '' ?> found.</p>
    <?php if ($questions): ?>
        <ul class="questions-list">
            <?php foreach ($questions as $q): ?>
                <li class="question-summary">
                    <div class="question-content">
                        <h3 class="question-title">
                            <a href="view_question.php?id=<?= $q['id'] ?>">
                                <?= htmlspecialchars($q['title']) ?>
                            </a>
                        </h3>
                        <div class="question-excerpt">
                            <?= nl2br(htmlspecialchars(mb_strimwidth($q['description'], 0, 200, '…'))) ?>
                        </div>
                        <div class="user-info">
                            Asked by <?= htmlspecialchars($q['username']) ?>
                            on <?= date('M j, Y', strtotime($q['created_at'])) ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

<?php else: ?>
    <?php
    // Fetch all tags with counts
    $stmt = $pdo->query("
        SELECT tag, COUNT(*) AS cnt
        FROM question_tags
        GROUP BY tag
        ORDER BY cnt DESC, tag ASC
    ");
    $tags = $stmt->fetchAll();
    ?>
    <ul>
        <?php foreach ($tags as $t): ?>
            <li>
                <a href="tags.php?tag=<?= urlencode($t['tag']) ?>">
                    <?= htmlspecialchars($t['tag']) ?>
                </a>
                (<?= (int)$t['cnt'] ?>)
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>