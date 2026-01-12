<?php
include '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['poll_id'], $data['user_id'], $data['date'], $data['vote_type'])) {
  echo json_encode(['error' => 'Invalid input']);
  exit;
}

$poll_id = $data['poll_id'];
$user_id = $data['user_id'];
$date = $data['date'];
$vote_type = $data['vote_type'];

if (!in_array($vote_type, ['YES', 'MAYBE', 'NO'])) {
  echo json_encode(['error' => 'Invalid vote type']);
  exit;
}

require_once '../config.php';

try {
  $pdo->beginTransaction();

  $pdo->prepare("INSERT IGNORE INTO USERS (user_id) VALUES (?)")->execute([$user_id]);

  // Fetch current vote
  $stmt = $pdo->prepare("SELECT vote_type FROM votes WHERE poll_id = ? AND user_id = ? AND voted_date = ?");
  $stmt->execute([$poll_id, $user_id, $date]);
  $existing = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing) {
    if ($vote_type === $existing['vote_type']) {
      // Same vote, do nothing
    } elseif ($vote_type === 'NO') {
      // Remove vote
      $pdo->prepare("DELETE FROM votes WHERE poll_id = ? AND user_id = ? AND voted_date = ?")->execute([$poll_id, $user_id, $date]);
    } else {
      // Change vote
      $pdo->prepare("UPDATE votes SET vote_type = ? WHERE poll_id = ? AND user_id = ? AND voted_date = ?")->execute([$vote_type, $poll_id, $user_id, $date]);
    }
  } else {
    if ($vote_type !== 'NO') {
      // Insert new vote
      $pdo->prepare("INSERT INTO votes (poll_id, user_id, voted_date, vote_type) VALUES (?, ?, ?, ?)")->execute([$poll_id, $user_id, $date, $vote_type]);
    }
    // If NO and no existing, do nothing
  }

  $pdo->commit();

  echo json_encode(['success' => true]);
} catch (Exception $e) {
  $pdo->rollBack();
  echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>