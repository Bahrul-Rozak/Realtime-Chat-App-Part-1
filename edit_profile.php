<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = htmlspecialchars($_POST['full_name']);
    $profile_picture = $_FILES['profile_picture'];

    if ($profile_picture['name'] != '') {
        $target_dir = "uploads/";
        $filename = time() . "_" . basename($profile_picture["name"]);
        $target_file = $target_dir . $filename;
        move_uploaded_file($profile_picture["tmp_name"], $target_file);

        $query = "UPDATE users SET full_name='$full_name', profile_picture='$filename' WHERE id=$user_id";
    } else {
        $query = "UPDATE users SET full_name='$full_name' WHERE id=$user_id";
    }

    mysqli_query($conn, $query);
    $msg = "✅ Profile updated!";
}

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5 col-md-6">
        <div class="card p-4 shadow">
            <h4 class="mb-4">Edit Profile</h4>
            <?php if ($msg): ?>
                <div class="alert alert-success"><?= $msg ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Profile Picture</label><br>
                    <img src="uploads/<?= $user['profile_picture'] ?>" alt="Profile" width="80" class="rounded-circle mb-2"><br>
                    <input type="file" name="profile_picture" class="form-control">
                </div>
                <button type="submit" class="btn btn-success">Save</button>
                <a href="chat.php" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</body>
</html>
