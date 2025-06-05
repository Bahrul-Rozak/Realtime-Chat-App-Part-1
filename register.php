<?php

session_start();
include "config.php";


if($_SERVER['REQUEST_METHOD'] == "POST"){
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if(empty($username) || empty($password)){
        $error = "Username dan Password Tidak Boleh Kosong...";
    }else{
        // cek username sudah digunakan atau belum
        $sql = "SELECT id FROM users WHERE username = '$username'";
        $result = $conn->query($sql);
    }

    if($result->num_rows > 0){
        $error = "username sudah digunakan";
    }else{
        // hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";
        if($conn->query($sql) == TRUE){
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit();
        }else{
            $error = "Error : " . $conn->error;
        }
    }
}


?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
  </head>
  <body class="bg-light">
    <div class="container mt-5" style="max-width: 400px;">
        <h3 class="mb-4 text-center">Daftar Akun</h3>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif;?>


        <form action="" method="POST">
            <div class="mb-3">
                <label for="username">Username</label>
                <input type="text" name="username" class="form-control">
            </div>
             <div class="mb-3">
                <label for="password">Password</label>
                <input type="password" name="password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary w-100">Daftar</button>
            <p class="mt-3 text-center">Sudah punya akun? <a href="login.php">Login disini</a></p>
        </form>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
  </body>
</html>