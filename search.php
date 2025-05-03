<?php
// search.php
require __DIR__ . '/config.php';
include __DIR__ . '/header.php';

$term = trim($_GET['q'] ?? '');
?>

<h2>Search Results for “<?= htmlspecialchars($term) ?>”</h2>

<?php if ($term === ''): ?>
    <p>Please enter a search term above.</p>
<?php else: ?>
    <?php
    // 1. Wildcard for LIKE
    $like = '%' . $term . '%';

    // 2. Query matching title OR description
    $stmt = $pdo->prepare("
    SELECT q.id, q.title, q.description, u.username, q.created_at
    FROM questions q
    JOIN users u ON q.user_id = u.id
    WHERE q.title LIKE :like
       OR q.description LIKE :like
    ORDER BY q.created_at DESC
  ");
    $stmt->execute([':like' => $like]);
    $results = $stmt->fetchAll();
    ?>

    <p><?= count($results) ?> result<?= count($results) !== 1 ? 's' : '' ?> found.</p>

    <?php if ($results): ?>
        <ul>
            <?php foreach ($results as $r): ?>
                <li style="margin-bottom:12px;">
                    <a href="view_question.php?id=<?= $r['id'] ?>">
                        <?= htmlspecialchars($r['title']) ?>
                    </a>
                    <br>
                    <span style="color:#666;font-size:.9em;">
                        Asked by <?= htmlspecialchars($r['username']) ?>
                        on <?= date('M j, Y', strtotime($r['created_at'])) ?>
                    </span>
                    <p style="margin:4px 0;">
                        <?= nl2br(htmlspecialchars(mb_strimwidth($r['description'], 0, 200, '…'))) ?>
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>