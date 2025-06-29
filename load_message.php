<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit();
}

$user_id = $_SESSION['user_id'];
$other_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($other_user_id <= 0) {
    echo json_encode([]);
    exit();
}

$sql = "SELECT sender_id, message, DATE_FORMAT(timestamp, '%H:%i') as timestamp 
        FROM messages 
        WHERE (sender_id = $user_id AND receiver_id = $other_user_id) 
        OR (sender_id = $other_user_id AND receiver_id = $user_id)
        ORDER BY timestamp ASC";

$result = $conn->query($sql);

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

header('Content-Type: application/json');
echo json_encode($messages);