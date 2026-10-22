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

$stmt = mysqli_prepare($koneksi, "SELECT * FROM prodi WHERE id_prodi = ?");
mysqli_stmt_bind_param($stmt, "i", $id_prodi);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: prodi.php?p=Data tidak ditemukan!");
    exit();
}

$error = "";

if (isset($_POST['update'])) {
    $kd_prodi = trim($_POST['kd_prodi'] ?? '');
    $nama_prodi = trim($_POST['nama_prodi'] ?? '');

    if ($kd_prodi === '' || $nama_prodi === '') {
        $error = "Data harus diisi!";
    } else {
        $stmt_cek = mysqli_prepare($koneksi, "SELECT id_prodi FROM prodi WHERE kd_prodi = ? AND id_prodi != ?");
        mysqli_stmt_bind_param($stmt_cek, "si", $kd_prodi, $id_prodi);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);

        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            $error = "Kode Prodi sudah digunakan!";
        } else {
            $stmt_up = mysqli_prepare($koneksi, "UPDATE prodi SET kd_prodi = ?, nama_prodi = ? WHERE id_prodi = ?");
            mysqli_stmt_bind_param($stmt_up, "ssi", $kd_prodi, $nama_prodi, $id_prodi);
            if (mysqli_stmt_execute($stmt_up)) {
                echo "<h1>Data berhasil diupdate!</h1>";
                echo "<a href='prodi.php' class='batal'>Kembali</a>";
                exit();
            } else {
                $error = "Gagal memperbarui data!";
            }
        }
    }
}
?>
<html>
<head>
    <title>Edit Prodi</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Prodi</h2>
        <form method="POST">
            <label>Kode Prodi :</label>
            <input type="text" name="kd_prodi" value="<?php echo $data['kd_prodi']; ?>" required><br>
            <br><label>Nama Prodi :</label>
            <input type="text" name="nama_prodi" value="<?php echo $data['nama_prodi']; ?>" required><br>
            <br><button type="submit" name="update" class="submit">UPDATE</button>
            <button type="submit" name="batal" class="cancel"><a href="prodi.php">BATAL</a></button>
    </form>
    </div>
</body>
</html>