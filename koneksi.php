<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "raffa_zidan_penilaian";

$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($koneksi, "utf8mb4");
?>
