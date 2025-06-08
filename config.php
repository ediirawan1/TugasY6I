<?php
session_start();

$host = "javavisual.jux.in";
$username = "javavisu_root";
$password = "JavaVisual123!@#";
$database = "javavisu_web";

$conn = mysqli_connect($host, $username, $password, $database);

    if (!$conn) {
        die("Koneksi gagal: " . mysqli_connect_error());
    }
?>
