<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: index.php?p=Silahkan login terlebih dahulu!");
    exit();
}
include "koneksi.php";
$error = "";

if (isset($_POST['simpan'])) {
    $kd_prodi = trim($_POST['kd_prodi'] ?? '');
    $nama_prodi = trim($_POST['nama_prodi'] ?? '');

    if ($kd_prodi === '' || $nama_prodi === '') {
        $error = "Data harus diisi!";
    } else {
        $stmt_cek = mysqli_prepare($koneksi, "SELECT id_prodi FROM prodi WHERE kd_prodi = ?");
        mysqli_stmt_bind_param($stmt_cek, "s", $kd_prodi);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);

        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            $error = "Kode Prodi sudah digunakan!";
        } else {
            $stmt_ins = mysqli_prepare($koneksi, "INSERT INTO prodi (kd_prodi, nama_prodi) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt_ins, "ss", $kd_prodi, $nama_prodi);
            if (mysqli_stmt_execute($stmt_ins)) {
                echo "<h1>Data berhasil disimpan!</h1>";
                echo "<a href='prodi.php' class='batal'>Kembali</a>";
                exit();
            } else {
                $error = "Gagal menyimpan data!";
            }
        }
    }
}
?>
<html>
<head>
    <title>Tambah Prodi</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Tambah Prodi</h2>
<form method="POST" action=""> 
    <label>Kode Prodi :</label>
    <input type="text" name="kd_prodi" required><br><br>
    <label>Nama Prodi :</label>
    <input type="text" name="nama_prodi" required><br><br>
    <button type="submit" name="simpan" class="submit">SIMPAN</button>
    <button type="submit" name="batal" class="cancel"><a href="prodi.php">BATAL</a></button>
</form>
    </div>
</body>
</html>