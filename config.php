<?php

$db_host = 'localhost';     // Biasanya 'localhost'
$db_user = 'root';          // User default XAMPP/WAMP
$db_pass = '';              // Password default XAMPP/WAMP (kosong)
$db_name = 'barbershop_db'; // Nama database Anda

// Membuat koneksi menggunakan mysqli
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

// Set charset (opsional tapi direkomendasikan)
$conn->set_charset("utf8mb4");

?>
