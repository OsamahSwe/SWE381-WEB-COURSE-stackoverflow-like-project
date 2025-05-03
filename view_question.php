<?php
// view_question.php
require __DIR__ . '/config.php';
include __DIR__ . '/header.php';

// 1. Get & validate the question ID
$qid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$qid) {
    echo '<p>Invalid question ID.</p>';
    include __DIR__ . '/footer.php';
    exit;
}

// 2. Fetch the question + author
$stmt = $pdo->prepare("
    SELECT q.*, u.username
    FROM questions q
    JOIN users u ON q.user_id = u.id
    WHERE q.id = ?
");
$stmt->execute([$qid]);
$question = $stmt->fetch();
if (!$question) {
    echo '<p>Question not found.</p>';
    include __DIR__ . '/footer.php';
    exit;
}

// 3. Fetch tags for this question
$tagStmt = $pdo->prepare("
    SELECT tag
    FROM question_tags
    WHERE question_id = ?
");
$tagStmt->execute([$qid]);
$tags = $tagStmt->fetchAll(PDO::FETCH_COLUMN);

// 4. Handle form submissions (comments or answers)
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    // comments
    if (isset($_POST['comment_type'])) {
        $content = trim($_POST['content'] ?? '');
        if ($content === '') {
            $errors[] = "Comment cannot be empty.";
        } else {
            if ($_POST['comment_type'] === 'question') {
                $ins = $pdo->prepare("
                    INSERT INTO question_comments (question_id, user_id, content)
                    VALUES (?, ?, ?)
                ");
                $ins->execute([$qid, $_SESSION['user_id'], $content]);
            } else {
                $aid = (int)($_POST['answer_id'] ?? 0);
                $ins = $pdo->prepare("
                    INSERT INTO answer_comments (answer_id, user_id, content)
                    VALUES (?, ?, ?)
                ");
                $ins->execute([$aid, $_SESSION['user_id'], $content]);
            }
            header("Location: view_question.php?id={$qid}");
            exit;
        }
    }
    // new answer
    else {
        $answerText = trim($_POST['content'] ?? '');
        if ($answerText === '') {
            $errors[] = "Answer cannot be empty.";
        } else {
            $ins = $pdo->prepare("
                INSERT INTO answers (question_id, user_id, content)
                VALUES (?, ?, ?)
            ");
            $ins->execute([$qid, $_SESSION['user_id'], $answerText]);
            header("Location: view_question.php?id={$qid}");
            exit;
        }
    }
}

// 5. Fetch question comments
$qcStmt = $pdo->prepare("
    SELECT qc.*, u.username
    FROM question_comments qc
    JOIN users u ON qc.user_id = u.id
    WHERE qc.question_id = ?
    ORDER BY qc.created_at ASC
");
$qcStmt->execute([$qid]);
$questionComments = $qcStmt->fetchAll();

// 6. Fetch answers + ratings only
$ansStmt = $pdo->prepare("
    SELECT 
      a.*, 
      u.username,
      ROUND(COALESCE(AVG(r.rating),0),1) AS avg_rating,
      COUNT(r.id)                         AS rating_count
    FROM answers a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN ratings r ON r.answer_id = a.id
    WHERE a.question_id = ?
    GROUP BY a.id
    ORDER BY a.created_at ASC
");
$ansStmt->execute([$qid]);
$answers = $ansStmt->fetchAll();

// 7. Fetch comments for each answer
$allAnswerComments = [];
foreach ($answers as $ans) {
    $acStmt = $pdo->prepare("
        SELECT ac.*, u.username
        FROM answer_comments ac
        JOIN users u ON ac.user_id = u.id
        WHERE ac.answer_id = ?
        ORDER BY ac.created_at ASC
    ");
    $acStmt->execute([$ans['id']]);
    $allAnswerComments[$ans['id']] = $acStmt->fetchAll();
}
?>

<article>
    <h2><?= htmlspecialchars($question['title']) ?></h2>

    <?php if ($tags): ?>
        <p>
            <?php foreach ($tags as $tag): ?>
                <a class="tag" href="tags.php?tag=<?= urlencode($tag) ?>">#<?= htmlspecialchars($tag) ?></a>
            <?php endforeach; ?>
        </p>
    <?php endif; ?>

    <p><?= nl2br(htmlspecialchars($question['description'])) ?></p>
    <p style="font-size:.9em;color:#666;">
        Asked by <strong><?= htmlspecialchars($question['username']) ?></strong>
        on <?= date('M j, Y \a\t H:i', strtotime($question['created_at'])) ?>
    </p>

    <?php if (
        isset($_SESSION['user_id']) &&
        $_SESSION['user_id'] === (int)$question['user_id']
    ): ?>
        <p>
            <a href="edit_question.php?id=<?= $qid ?>">✎ Edit</a> |
            <a href="delete_question.php?id=<?= $qid ?>"
               onclick="return confirm('Delete this question?');">🗑 Delete</a>
        </p>
    <?php endif; ?>
</article>

<hr>

<!-- QUESTION COMMENTS -->
<section>
    <h3><?= count($questionComments) ?> Comment<?= count($questionComments) !== 1 ? 's' : '' ?></h3>

    <?php foreach ($questionComments as $qc): ?>
        <div class="comment-block">
            <p><?= nl2br(htmlspecialchars($qc['content'])) ?></p>
            <p class="comment-meta" style="font-size:.8em;color:#666;">
                <?= htmlspecialchars($qc['username']) ?> on
                <?= date('M j, Y \a\t H:i', strtotime($qc['created_at'])) ?>
            </p>
            <?php if (
                isset($_SESSION['user_id']) &&
                $_SESSION['user_id'] === (int)$qc['user_id']
            ): ?>
                <p class="comment-actions">
                    <a href="edit_question_comment.php?id=<?= $qc['id'] ?>">✎ Edit</a> |
                    <a href="delete_question_comment.php?id=<?= $qc['id'] ?>"
                       onclick="return confirm('Delete this comment?');">🗑 Delete</a>
                </p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form class="comment-form" method="post" action="view_question.php?id=<?= $qid ?>">
            <input type="hidden" name="comment_type" value="question">
            <textarea name="content" rows="3" placeholder="Add a comment…"></textarea><br>
            <button type="submit">Post Comment</button>
        </form>
    <?php else: ?>
        <p><a href="login.php">Log in</a> to comment.</p>
    <?php endif; ?>
</section>

<hr>

<!-- ANSWERS -->
<section>
    <h3><?= count($answers) ?> Answer<?= count($answers) !== 1 ? 's' : '' ?></h3>

    <?php foreach ($answers as $ans): ?>
        <div class="answer-block">
            <p><?= nl2br(htmlspecialchars($ans['content'])) ?></p>
            <p class="answer-meta" style="font-size:.8em;color:#666;">
                Answered by <strong><?= htmlspecialchars($ans['username']) ?></strong>
                on <?= date('M j, Y \a\t H:i', strtotime($ans['created_at'])) ?>
            </p>

            <p class="rating">
                <strong>Rating:</strong> <?= htmlspecialchars($ans['avg_rating']) ?>
                (<?= $ans['rating_count'] ?>)
                <?php if (isset($_SESSION['user_id'])): ?>
                    • Rate:
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <a href="rate_answer.php?id=<?= $ans['id'] ?>&r=<?= $i ?>"
                           onclick="return confirm('Rate <?= $i ?> star(s)?');">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                <?php endif; ?>
            </p>

            <?php if (
                isset($_SESSION['user_id']) &&
                $_SESSION['user_id'] === (int)$ans['user_id']
            ): ?>
                <p>
                    <a href="edit_answer.php?id=<?= $ans['id'] ?>">✎ Edit</a> |
                    <a href="delete_answer.php?id=<?= $ans['id'] ?>"
                       onclick="return confirm('Delete this answer?');">🗑 Delete</a>
                </p>
            <?php endif; ?>

            <div class="comments-for-answer">
                <strong>
                    <?= count($allAnswerComments[$ans['id']]) ?>
                    Comment<?= count($allAnswerComments[$ans['id']]) !== 1 ? 's' : '' ?>
                </strong>
                <?php foreach ($allAnswerComments[$ans['id']] as $ac): ?>
                    <div class="comment-block">
                        <p><?= nl2br(htmlspecialchars($ac['content'])) ?></p>
                        <p class="comment-meta" style="font-size:.8em;color:#666;">
                            <?= htmlspecialchars($ac['username']) ?> on
                            <?= date('M j, Y \a\t H:i', strtotime($ac['created_at'])) ?>
                        </p>
                        <?php if (
                            isset($_SESSION['user_id']) &&
                            $_SESSION['user_id'] === (int)$ac['user_id']
                        ): ?>
                            <p class="comment-actions">
                                <a href="edit_answer_comment.php?id=<?= $ac['id'] ?>">✎ Edit</a> |
                                <a href="delete_answer_comment.php?id=<?= $ac['id'] ?>"
                                   onclick="return confirm('Delete this comment?');">🗑 Delete</a>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form class="comment-form" method="post" action="view_question.php?id=<?= $qid ?>">
                        <input type="hidden" name="comment_type" value="answer">
                        <input type="hidden" name="answer_id" value="<?= $ans['id'] ?>">
                        <textarea name="content" rows="2" placeholder="Add a comment…"></textarea><br>
                        <button type="submit">Post Comment</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <hr>
    <?php endforeach; ?>
</section>

<!-- YOUR ANSWER -->
<section>
    <h3>Your Answer</h3>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <p><a href="login.php">Log in</a> to post an answer.</p>
    <?php else: ?>
        <?php if (!isset($_POST['comment_type']) && $errors): ?>
            <ul style="color:red;">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form class="answer-form" method="post" action="view_question.php?id=<?= $qid ?>">
            <textarea name="content" rows="6" placeholder="Type your answer here…"></textarea><br>
            <button type="submit">Post Answer</button>
        </form>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/footer.php'; ?>
