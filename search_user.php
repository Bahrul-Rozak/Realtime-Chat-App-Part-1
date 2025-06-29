<?php
session_start();

include 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit();
}

$user_id = $_SESSION['user_id'];

$search = '';
if (isset($_GET['q'])) {
    $search = mysqli_real_escape_string($conn, $_GET['q']);
}

$query = "SELECT id, username, full_name, profile_picture, last_seen 
          FROM users 
          WHERE id != $user_id 
          AND (username LIKE '%$search%' OR full_name LIKE '%$search%')";

$result = mysqli_query($conn, $query);

while ($user = mysqli_fetch_assoc($result)) {
    $last_seen = strtotime($user['last_seen']);
    $status = (time() - $last_seen) < 60 ? 'Online' : 'Offline';

    echo '<div class="user-item" data-userid="' . $user['id'] . '">';
    echo '<div class="avatar">';
    if ($user['profile_picture']) {
        echo '<img src="uploads/' . $user['profile_picture'] . '" class="rounded-circle" width="40" height="40">';
    } else {
        echo strtoupper($user['username'][0]);
    }
    echo '</div>';
    echo '<div class="username">' . htmlspecialchars($user['full_name'] ?: $user['username']) . '</div>';
    echo '<div class="last-seen">' . $status . '</div>';
    echo '</div>';
}
