<?php
// vote_answer.php
require __DIR__ . '/config.php';

// 1. Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2. Get & validate inputs
$aid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$v   = isset($_GET['v'])  ? (int)$_GET['v']  : 0;
if (!$aid || !in_array($v, [1, -1], true)) {
    header('Location: index.php');
    exit;
}

// 3. Record the vote (insert or update)
$stmt = $pdo->prepare("
  INSERT INTO answer_votes (answer_id, user_id, vote)
  VALUES (?, ?, ?)
  ON DUPLICATE KEY UPDATE vote = VALUES(vote)
");
$stmt->execute([$aid, $_SESSION['user_id'], $v]);

// 4. Find the question ID to redirect back
$qStmt = $pdo->prepare("SELECT question_id FROM answers WHERE id = ?");
$qStmt->execute([$aid]);
$q   = $qStmt->fetch();
$qid = $q ? (int)$q['question_id'] : 0;

// 5. Redirect back to the question view
header("Location: view_question.php?id={$qid}");
exit;
