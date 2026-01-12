<?php
include '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
  echo json_encode(['error' => 'Missing user_id']);
  exit;
}

require_once '../config.php';

try {
  $stmt = $pdo->prepare("SELECT poll_id, name, create_date FROM polls WHERE creating_user = ? ORDER BY create_date DESC");
  $stmt->execute([$user_id]);
  $polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($polls);
} catch (Exception $e) {
  echo json_encode(['error' => $e->getMessage()]);
}
?>