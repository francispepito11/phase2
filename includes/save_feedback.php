<?php
// save_feedback.php: Save client satisfaction feedback to the database
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $support_request_id = isset($_POST['support_request_id']) ? (int)$_POST['support_request_id'] : null;
    $client_name = isset($_POST['client_name']) ? trim($_POST['client_name']) : '';

    if (!$rating || $rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Invalid rating.']);
        exit;
    }

    $sql = "INSERT INTO client_feedback (support_request_id, client_name, rating, comment, submitted_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('isis', $support_request_id, $client_name, $rating, $comment);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid request.']);
