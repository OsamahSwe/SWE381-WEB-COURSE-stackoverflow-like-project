<?php
// edit_question.php
require __DIR__ . '/config.php';

// 1. Get & validate question ID
$qid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$qid) {
    header('Location: index.php');
    exit;
}

// 2. Fetch the question
$stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
$stmt->execute([$qid]);
$question = $stmt->fetch();
if (!$question) {
    header('Location: index.php');
    exit;
}

// 3. Ensure current user is the author
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== (int)$question['user_id']) {
    header('HTTP/1.1 403 Forbidden');
    echo "<p>You don't have permission to edit this question.</p>";
    exit;
}

include __DIR__ . '/header.php';

$errors = [];

// 4. Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']       ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '') {
        $errors[] = "Title cannot be empty.";
    }

    if (empty($errors)) {
        $upd = $pdo->prepare("
            UPDATE questions
            SET title = ?, description = ?
            WHERE id = ?
        ");
        $upd->execute([$title, $description, $qid]);

        header("Location: view_question.php?id={$qid}");
        exit;
    }
} else {
    // 5. On initial GET, prefills
    $title       = $question['title'];
    $description = $question['description'];
}
?>

<h2>Edit Question</h2>

<?php if ($errors): ?>
    <ul style="color: red;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="edit_question.php?id=<?= $qid ?>">
    <label>
        Title:<br>
        <input type="text" name="title" value="<?= htmlspecialchars($title) ?>" style="width:100%;">
    </label>
    <br><br>

    <label>
        Description:<br>
        <textarea name="description" rows="6" style="width:100%;"><?= htmlspecialchars($description) ?></textarea>
    </label>
    <br><br>

    <button type="submit">Save Changes</button>
    <a href="view_question.php?id=<?= $qid ?>">Cancel</a>
</form>

<?php include __DIR__ . '/footer.php'; ?>