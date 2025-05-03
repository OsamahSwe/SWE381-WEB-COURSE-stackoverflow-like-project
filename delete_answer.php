<?php
// delete_answer.php
require __DIR__ . '/config.php';

// 1. Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2. Get & validate answer ID
$aid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$aid) {
    header('Location: index.php');
    exit;
}

// 3. Fetch answer and confirm ownership (and get question_id)
$stmt = $pdo->prepare("SELECT user_id, question_id FROM answers WHERE id = ?");
$stmt->execute([$aid]);
$row = $stmt->fetch();
if (!$row || (int)$row['user_id'] !== $_SESSION['user_id']) {
    header('HTTP/1.1 403 Forbidden');
    exit('You do not have permission to delete this answer.');
}
$qid = (int)$row['question_id'];

// 4. Delete ratings manually (no cascade)
$pdo->beginTransaction();
try {
    $pdo->prepare("DELETE FROM ratings WHERE answer_id = ?")
        ->execute([$aid]);

    // 5. Delete the answer (will cascade votes & comments)
    $pdo->prepare("DELETE FROM answers WHERE id = ?")
        ->execute([$aid]);

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    exit('Error deleting answer: ' . htmlspecialchars($e->getMessage()));
}

// 6. Redirect back to the question view
header("Location: view_question.php?id={$qid}");
exit;
