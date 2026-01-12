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

if (!$data || !isset($data['poll_id']) || !isset($data['user_id']) || !isset($data['nickname'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$poll_id = $data['poll_id'];
$user_id = $data['user_id'];
$nickname = trim($data['nickname']);

if (!$nickname) {
    echo json_encode(['error' => 'Nickname cannot be empty']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Ensure user exists
    $pdo->prepare("INSERT IGNORE INTO USERS (user_id) VALUES (?)")->execute([$user_id]);

    // Check if participant exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM poll_participants WHERE poll_id = ? AND user_id = ?");
    $stmt->execute([$poll_id, $user_id]);
    $exists = $stmt->fetchColumn() > 0;

    if (!$exists) {
        // Generate random color
        $color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        // Insert new participant
        $stmt = $pdo->prepare("INSERT INTO poll_participants (poll_id, user_id, user_color, nickname) VALUES (?, ?, ?, ?)");
        $stmt->execute([$poll_id, $user_id, $color, $nickname]);
    } else {
        // Update existing nickname
        $stmt = $pdo->prepare("UPDATE poll_participants SET nickname = ? WHERE poll_id = ? AND user_id = ?");
        $stmt->execute([$nickname, $poll_id, $user_id]);
    }

    $pdo->commit();

    // Fetch the participant data
    $stmt = $pdo->prepare("SELECT user_id, nickname, user_color FROM poll_participants WHERE poll_id = ? AND user_id = ?");
    $stmt->execute([$poll_id, $user_id]);
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'participant' => $participant]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>