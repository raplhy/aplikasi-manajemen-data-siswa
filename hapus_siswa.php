<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: index.php?p=Silahkan login terlebih dahulu!");
    exit();
}
include "koneksi.php";

$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: siswa.php?p=ID Siswa tidak valid!");
    exit();
}

$stmt_cek = mysqli_prepare($koneksi, "SELECT foto FROM siswa WHERE id = ?");
mysqli_stmt_bind_param($stmt_cek, "i", $id);
mysqli_stmt_execute($stmt_cek);
$res_cek = mysqli_stmt_get_result($stmt_cek);
$data = mysqli_fetch_assoc($res_cek);

if (!$data) {
    header("Location: siswa.php?p=Data tidak ditemukan!");
    exit();
}

if (!empty($data['foto']) && file_exists("uploads/" . $data['foto'])) {
    unlink("uploads/" . $data['foto']);
}

$stmt_del = mysqli_prepare($koneksi, "DELETE FROM siswa WHERE id = ?");
mysqli_stmt_bind_param($stmt_del, "i", $id);
$hapus = mysqli_stmt_execute($stmt_del);

if ($hapus) {
    echo "<h1>Data berhasil dihapus!</h1>";
    echo "<a href='siswa.php' class='batal'>Kembali</a>";
} else {
    header("Location: siswa.php?p=Gagal menghapus data!");
    exit();
}
?>