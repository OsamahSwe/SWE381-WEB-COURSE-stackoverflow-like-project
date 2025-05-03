<?php
// edit_answer_comment.php
require __DIR__ . '/config.php';
include __DIR__ . '/header.php';

$cid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$cid) {
    echo "<p>Invalid comment ID.</p>";
    include __DIR__ . '/footer.php';
    exit;
}

// fetch the comment + owning answer + question id
$stmt = $pdo->prepare("
    SELECT ac.*, a.question_id, q.title
    FROM answer_comments ac
    JOIN answers a   ON a.id = ac.answer_id
    JOIN questions q ON q.id = a.question_id
    WHERE ac.id = ?
");
$stmt->execute([$cid]);
$com = $stmt->fetch();

if (!$com) {
    echo "<p>Comment not found.</p>";
    include __DIR__ . '/footer.php';
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== (int)$com['user_id']) {
    echo "<p>You are not allowed to edit this comment.</p>";
    include __DIR__ . '/footer.php';
    exit;
}

$errors  = [];
$content = $com['content'];

// update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $content = trim($_POST['content'] ?? '');
    if ($content === '') {
        $errors[] = "Comment cannot be empty.";
    } else {
        $upd = $pdo->prepare("UPDATE answer_comments SET content = ? WHERE id = ?");
        $upd->execute([$content, $cid]);
        header("Location: view_question.php?id={$com['question_id']}");
        exit;
    }
}

// delete
if (isset($_POST['delete'])) {
    $del = $pdo->prepare("DELETE FROM answer_comments WHERE id = ?");
    $del->execute([$cid]);
    header("Location: view_question.php?id={$com['question_id']}");
    exit;
}
?>

<h2>Edit Comment on: “<?= htmlspecialchars($com['title']) ?>”</h2>

<?php if ($errors): ?>
    <ul class="errors"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
<?php endif; ?>

<form method="post">
    <textarea name="content" rows="4"><?= htmlspecialchars($content) ?></textarea><br>
    <button type="submit" name="save">Save</button>
    <button type="submit" name="delete"
        onclick="return confirm('Delete this comment?');">Delete</button>
    <a href="view_question.php?id=<?= $com['question_id'] ?>">Cancel</a>
</form>

<?php include __DIR__ . '/footer.php'; ?>