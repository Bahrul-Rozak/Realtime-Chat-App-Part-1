<?php
$host = "localhost";
$user = "root";        
$password = "";       
$dbname = "realtime_chat_php";

// Buat koneksi
$conn = new mysqli($host, $user, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal sayang: " . $conn->connect_error);
}
?>
