<?php
// edit_answer.php
require __DIR__ . '/config.php';

// 1. Get & validate answer ID
$aid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$aid) {
    header('Location: index.php');
    exit;
}

// 2. Fetch the answer
$stmt = $pdo->prepare("SELECT * FROM answers WHERE id = ?");
$stmt->execute([$aid]);
$answer = $stmt->fetch();
if (!$answer) {
    header('Location: index.php');
    exit;
}

// 3. Ensure current user is the author
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== (int)$answer['user_id']) {
    header('HTTP/1.1 403 Forbidden');
    echo "<p>You don't have permission to edit this answer.</p>";
    exit;
}

include __DIR__ . '/header.php';

$errors = [];

// 4. Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    if ($content === '') {
        $errors[] = "Answer cannot be empty.";
    }
    if (empty($errors)) {
        $upd = $pdo->prepare("
            UPDATE answers
            SET content = ?
            WHERE id = ?
        ");
        $upd->execute([$content, $aid]);

        // Redirect back to the question view
        header("Location: view_question.php?id=" . (int)$answer['question_id']);
        exit;
    }
} else {
    // Prefill form on GET
    $content = $answer['content'];
}
?>

<h2>Edit Your Answer</h2>

<?php if ($errors): ?>
    <ul style="color: red;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="edit_answer.php?id=<?= $aid ?>">
    <textarea name="content" rows="6" style="width:100%;"><?= htmlspecialchars($content) ?></textarea>
    <br><br>
    <button type="submit">Save Changes</button>
    <a href="view_question.php?id=<?= (int)$answer['question_id'] ?>">Cancel</a>
</form>

<?php include __DIR__ . '/footer.php'; ?>