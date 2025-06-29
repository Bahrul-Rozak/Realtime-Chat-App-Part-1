<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if ($receiver_id <= 0 || empty($message)) {
    http_response_code(400);
    echo 'invalid';
    exit();
}

$message = $conn->real_escape_string($message);

$sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES ($user_id, $receiver_id, '$message')";

if ($conn->query($sql) === TRUE) {
    echo 'success';
} else {
    echo 'error';
}
