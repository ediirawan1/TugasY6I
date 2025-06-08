<?php
require 'config.php';
session_start();

// Cek login
if (!isset($_SESSION['log'])) {
    header('location:login.php'); 
    exit();
}

// // Proses tambah data barang
// if (isset($_POST['addDataBarang'])) {
//     $namaBarang = trim($_POST['nama_barang']);
//     $jumlahBarang = intval($_POST['jumlah_barang']);

//     if (empty($namaBarang) || $jumlahBarang <= 0) {
//         echo "<script>alert('Nama atau jumlah barang tidak valid!');</script>";
//     } else {
//         $stmt = mysqli_prepare($conn, "INSERT INTO data_barang (nama_barang, jumlah_barang) VALUES (?, ?)");
//         if ($stmt) {
//             mysqli_stmt_bind_param($stmt, "si", $namaBarang, $jumlahBarang);
//             if (mysqli_stmt_execute($stmt)) {
//                 echo "<script>
//                     alert('Data berhasil ditambahkan!');
//                     window.location.href = 'index.php';
//                 </script>";
//                 exit();
//             } else {
//                 echo "<script>alert('Gagal menambahkan data: " . mysqli_stmt_error($stmt) . "');</script>";
//             }
//         } else {
//             echo "<script>alert('Statement tidak bisa dipersiapkan.');</script>";
//         }
//     }
// }
// ?>
