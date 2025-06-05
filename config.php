<?php

$host = "localhost";
$user = "root";
$password = "";
$dbname = "realtime_chat_php";

// buat koneksi
$conn = new mysqli($host,$user,$password,$dbname);

// cek koneksi
if($conn->connect_error){
    die("Koneksi Gagal" . $conn->connect_error);
}

?>