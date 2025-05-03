<?php
// rate_answer.php
require __DIR__ . '/config.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$aid = (int)($_GET['id'] ?? 0);
$r   = (int)($_GET['r']  ?? 0);
if (!$aid || $r < 1 || $r > 5) {
  header('Location: index.php');
  exit;
}
// Upsert rating
$stmt = $pdo->prepare("
  INSERT INTO ratings (answer_id, user_id, rating)
  VALUES (?, ?, ?)
  ON DUPLICATE KEY UPDATE rating = VALUES(rating)
");
$stmt->execute([$aid, $_SESSION['user_id'], $r]);
// Redirect back
$qStmt = $pdo->prepare("SELECT question_id FROM answers WHERE id = ?");
$qStmt->execute([$aid]);
$qid = (int)$qStmt->fetchColumn();
header("Location: view_question.php?id={$qid}");
exit;
