<?php
// ask_question.php
require __DIR__ . '/config.php';   // starts session + gives you $pdo

// 1. Protect this page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2. Grab the “dictionary” of all existing tags
$allTags = $pdo
    ->query("SELECT tag, COUNT(*) AS cnt FROM question_tags GROUP BY tag ORDER BY cnt DESC")
    ->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$title       = '';
$description = '';
$tagsInput   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 3. Collect & trim form input
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $tagsInput   = trim($_POST['tags'] ?? '');

    // 4. Validate
    if ($title === '') {
        $errors[] = "Title is required.";
    }

    if (empty($errors)) {
        // 5. Insert question
        $pdo->beginTransaction();
        $insQ = $pdo->prepare("
            INSERT INTO questions (user_id, title, description, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $insQ->execute([
            $_SESSION['user_id'],
            $title,
            $description
        ]);
        $qid = $pdo->lastInsertId();

        // 6. Insert tags
        if ($tagsInput !== '') {
            $tagsArr = array_unique(
                array_filter(
                    array_map('trim', explode(',', $tagsInput))
                )
            );
            $insT = $pdo->prepare("
                INSERT INTO question_tags (question_id, tag)
                VALUES (?, ?)
            ");
            foreach ($tagsArr as $tag) {
                $insT->execute([$qid, $tag]);
            }
        }

        $pdo->commit();

        // 7. Redirect to view
        header("Location: view_question.php?id={$qid}");
        exit;
    }
}

include __DIR__ . '/header.php';   // opens <main>
?>

<h2>Ask a Question</h2>

<?php if ($errors): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="ask_question.php" style="max-width:700px">
    <label>
        Title:<br>
        <input
            type="text"
            name="title"
            value="<?= htmlspecialchars($title) ?>"
            style="width:100%">
    </label>
    <br><br>

    <label>
        Description:<br>
        <textarea
            name="description"
            rows="6"
            style="width:100%;"><?= htmlspecialchars($description) ?></textarea>
    </label>
    <br><br>

    <label>
        Tags (comma-separated):<br>
        <input
            type="text"
            name="tags"
            placeholder="e.g. php, mysql, css"
            value="<?= htmlspecialchars($tagsInput) ?>"
            style="width:100%">
    </label>

    <?php if ($allTags): ?>
        <p style="margin-top:4px;font-size:0.9em;color:#555;">
            Available tags:
            <?php foreach ($allTags as $t): ?>
                <span
                    class="tag-suggest"
                    title="used <?= $t['cnt'] ?>×"
                    onclick="
                      // when clicked, add/remove in the input field
                      let input = this.closest('form').tags;
                      let current = input.value.split(',').map(s=>s.trim()).filter(s=>s);
                      let tag = '<?= htmlspecialchars($t['tag'], ENT_QUOTES) ?>';
                      let i = current.indexOf(tag);
                      if (i !== -1) current.splice(i,1);
                      else current.push(tag);
                      input.value = current.join(', ');
                    "
                ><?= htmlspecialchars($t['tag']) ?></span>
            <?php endforeach; ?>
        </p>
    <?php endif; ?>

    <br>
    <button type="submit">Post Your Question</button>
</form>

<?php include __DIR__ . '/footer.php';  // closes </main> ?>
