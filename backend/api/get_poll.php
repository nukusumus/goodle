<?php
include '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$poll_id = $_GET['poll_id'] ?? null;

if (!$poll_id) {
    echo json_encode(['error' => 'Missing poll_id']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM polls WHERE poll_id = ?");
    $stmt->execute([$poll_id]);
    $poll = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$poll) {
        echo json_encode(['error' => 'Poll not found']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT user_id, nickname, user_color FROM poll_participants WHERE poll_id = ?");
    $stmt->execute([$poll_id]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT voted_date, user_id, vote_type FROM votes WHERE poll_id = ?");
    $stmt->execute([$poll_id]);
    $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['poll' => $poll, 'participants' => $participants, 'votes' => $votes]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>