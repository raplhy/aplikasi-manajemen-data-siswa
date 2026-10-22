<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: index.php?p=Silahkan login terlebih dahulu!");
    exit();
}
include "koneksi.php";

$id_prodi = filter_var($_GET['id_prodi'] ?? 0, FILTER_VALIDATE_INT);
if (!$id_prodi) {
    header("Location: prodi.php?p=ID Prodi tidak valid!");
    exit();
}

$stmt_q = mysqli_prepare($koneksi, "SELECT kd_prodi FROM prodi WHERE id_prodi = ?");
mysqli_stmt_bind_param($stmt_q, "i", $id_prodi);
mysqli_stmt_execute($stmt_q);
$res_q = mysqli_stmt_get_result($stmt_q);
$dp = mysqli_fetch_assoc($res_q);

if (!$dp) {
    header("Location: prodi.php?p=Data tidak ditemukan!");
    exit();
}

$kd_prodi = $dp['kd_prodi'];
$stmt_cek = mysqli_prepare($koneksi, "SELECT id FROM siswa WHERE kd_prodi = ?");
mysqli_stmt_bind_param($stmt_cek, "s", $kd_prodi);
mysqli_stmt_execute($stmt_cek);
mysqli_stmt_store_result($stmt_cek);

if (mysqli_stmt_num_rows($stmt_cek) > 0) {
    echo "<p style='color: red; font-weight: bold;'>Data tidak bisa dihapus karena masih digunakan!</p>";
    echo "<a href='prodi.php' class='batal'>Kembali</a>";
} else {
    $stmt_del = mysqli_prepare($koneksi, "DELETE FROM prodi WHERE id_prodi = ?");
    mysqli_stmt_bind_param($stmt_del, "i", $id_prodi);
    mysqli_stmt_execute($stmt_del);
    echo "<h1>Data berhasil dihapus!</h1>";
    echo "<a href='prodi.php' class='batal'>Kembali</a>";
}
exit();
?>