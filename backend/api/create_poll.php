<?php
include '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['poll_name']) || !isset($input['user_id'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$poll_id = uniqid('poll_', true);

try {
    // Insert into USERS if not exists
    $stmt = $pdo->prepare("INSERT IGNORE INTO USERS (user_id) VALUES (?)");
    $stmt->execute([$input['user_id']]);

    // Insert into polls
    $stmt = $pdo->prepare("INSERT INTO polls (poll_id, name, creating_user, create_date) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$poll_id, $input['poll_name'], $input['user_id']]);

    // Generate random color
    $color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);

    // Default nickname
    $nickname = 'User' . substr($input['user_id'], 0, 4);

    // Insert into poll_participants
    $stmt = $pdo->prepare("INSERT INTO poll_participants (poll_id, user_id, user_color, nickname) VALUES (?, ?, ?, ?)");
    $stmt->execute([$poll_id, $input['user_id'], $color, $nickname]);

    echo json_encode(['success' => true, 'poll_id' => $poll_id]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>