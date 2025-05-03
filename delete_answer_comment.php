<?php
// delete_answer_comment.php
require __DIR__ . '/config.php';

// 1. Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2. Get comment ID
$cid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$cid) {
    header('Location: index.php');
    exit;
}

// 3. Fetch & authorize
$stmt = $pdo->prepare("SELECT answer_id, user_id FROM answer_comments WHERE id = ?");
$stmt->execute([$cid]);
$comment = $stmt->fetch();
if (!$comment || (int)$comment['user_id'] !== $_SESSION['user_id']) {
    header('HTTP/1.1 403 Forbidden');
    exit('No permission to delete.');
}

// 4. Delete and redirect
$pdo->prepare("DELETE FROM answer_comments WHERE id = ?")
    ->execute([$cid]);

// need question_id to go back
$qstmt = $pdo->prepare("
  SELECT question_id FROM answers WHERE id = ?
");
$qstmt->execute([(int)$comment['answer_id']]);
$q = $qstmt->fetchColumn();

header("Location: view_question.php?id=" . (int)$q);
exit;
