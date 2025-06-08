<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$database = "inventory_web";

$conn = mysqli_connect($host, $username, $password, $database);

    if (!$conn) {
        die("Koneksi gagal: " . mysqli_connect_error());
    }
?>
