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

if (!$data || !isset($data['poll_id']) || !isset($data['user_id'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$poll_id = $data['poll_id'];
$user_id = $data['user_id'];

try {
    $pdo->beginTransaction();

    // Check if user is creator
    $stmt = $pdo->prepare("SELECT creating_user FROM polls WHERE poll_id = ?");
    $stmt->execute([$poll_id]);
    $poll = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$poll || $poll['creating_user'] !== $user_id) {
        echo json_encode(['error' => 'Not authorized']);
        $pdo->rollBack();
        exit;
    }

    // Delete related records first
    $stmt = $pdo->prepare("DELETE FROM votes WHERE poll_id = ?");
    $stmt->execute([$poll_id]);

    $stmt = $pdo->prepare("DELETE FROM poll_participants WHERE poll_id = ?");
    $stmt->execute([$poll_id]);

    // Then delete poll
    $stmt = $pdo->prepare("DELETE FROM polls WHERE poll_id = ?");
    $stmt->execute([$poll_id]);

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>