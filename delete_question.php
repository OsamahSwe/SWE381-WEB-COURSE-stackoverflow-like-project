<?php
// delete_question.php
require __DIR__ . '/config.php';

// 1. Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2. Get & validate question ID
$qid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$qid) {
    header('Location: index.php');
    exit;
}

// 3. Fetch the question and confirm ownership
$stmt = $pdo->prepare("SELECT user_id FROM questions WHERE id = ?");
$stmt->execute([$qid]);
$owner = $stmt->fetchColumn();
if (!$owner || (int)$owner !== $_SESSION['user_id']) {
    header('HTTP/1.1 403 Forbidden');
    exit('You do not have permission to delete this question.');
}

// 4. Manually delete all dependent rows inside a transaction
$pdo->beginTransaction();
try {
    // 4a. Delete all answer votes
    $pdo->prepare("
        DELETE av
        FROM answer_votes av
        JOIN answers a ON av.answer_id = a.id
        WHERE a.question_id = ?
    ")->execute([$qid]);

    // 4b. Delete all answer ratings (if you’re using the ratings table)
    $pdo->prepare("
        DELETE r
        FROM ratings r
        JOIN answers a ON r.answer_id = a.id
        WHERE a.question_id = ?
    ")->execute([$qid]);

    // 4c. Delete all answer comments
    $pdo->prepare("
        DELETE ac
        FROM answer_comments ac
        JOIN answers a ON ac.answer_id = a.id
        WHERE a.question_id = ?
    ")->execute([$qid]);

    // 4d. Delete all answers
    $pdo->prepare("
        DELETE FROM answers
        WHERE question_id = ?
    ")->execute([$qid]);

    // 4e. Delete all question comments
    $pdo->prepare("
        DELETE FROM question_comments
        WHERE question_id = ?
    ")->execute([$qid]);

    // 4f. Delete all question votes
    $pdo->prepare("
        DELETE FROM question_votes
        WHERE question_id = ?
    ")->execute([$qid]);

    // 4g. Finally, delete the question itself
    $pdo->prepare("
        DELETE FROM questions
        WHERE id = ?
    ")->execute([$qid]);

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    exit('Error deleting question: ' . htmlspecialchars($e->getMessage()));
}

// 5. Redirect back to “My Questions” (absolute path)
header('Location: /stackoverflow_clone/my_questions.php');
exit;
