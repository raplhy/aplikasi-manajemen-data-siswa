<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: index.php?p=Silahkan login terlebih dahulu!");
    exit();
}
include "koneksi.php";
$prodi = mysqli_query($koneksi, "SELECT * FROM prodi");
$error = "";

if (isset($_POST['simpan'])) {
    $nis = trim($_POST['nis'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $tahun_ajaran = trim($_POST['tahun_ajaran'] ?? '');
    $kd_prodi = trim($_POST['kd_prodi'] ?? '');
    $jk = trim($_POST['jenis_kelamin'] ?? '');
    $nama_foto = null;

    if (empty($nis) || empty($nama) || empty($kelas) || empty($tahun_ajaran) || empty($kd_prodi) || empty($jk)) {
        $error = "Data wajib diisi!";
    } else {
        if (!empty($_FILES['foto']['name'])) {
            $foto_name = $_FILES['foto']['name'];
            $tmp = $_FILES['foto']['tmp_name'];
            $file_size = $_FILES['foto']['size'];
            $ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (!in_array($ext, $allowed_ext)) {
                $error = "Format file foto tidak diizinkan! (Hanya JPG, JPEG, PNG, WEBP, GIF)";
            } elseif ($file_size > 2 * 1024 * 1024) {
                $error = "Ukuran file foto maksimal 2MB!";
            } else {
                if (!is_dir("uploads")) {
                    mkdir("uploads", 0777, true);
                }
                $nama_foto = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $path = "uploads/" . $nama_foto;

                if (!move_uploaded_file($tmp, $path)) {
                    $error = "Gagal mengupload foto!";
                }
            }
        }

        if ($error === "") {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO siswa (nis, nama, kelas, tahun_ajaran, kd_prodi, jenis_kelamin, foto) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssssss", $nis, $nama, $kelas, $tahun_ajaran, $kd_prodi, $jk, $nama_foto);

            if (mysqli_stmt_execute($stmt)) {
                echo "<h1>Data berhasil disimpan!</h1>";
                echo "<a href='siswa.php' class='batal'>Kembali</a>";
                exit();
            } else {
                $error = "Gagal menyimpan data ke database!";
            }
        }
    }
}
?>
<html>
<head>
    <title>Tambah Data Siswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>TAMBAH DATA SISWA</h2>
        <hr>
        <?php if($error != "") echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST" enctype="multipart/form-data">
            <table>
                <tr>
                    <td>NIS</td>
                    <td><input type="text" name="nis" required></td>
                </tr>
                <tr>
                    <td>Nama</td>
                    <td><input type="text" name="nama" required></td>
                </tr>
                <tr>
                    <td>Kelas</td>
                    <td><input type="text" name="kelas" required></td>
                </tr>
                <tr>
                    <td>Tahun Ajaran</td>
                    <td><input type="text" name="tahun_ajaran" required></td>
                </tr>
                <tr>
                    <td>Prodi</td>
                    <td>
                        <select name="kd_prodi" required>
                            <?php while ($p = mysqli_fetch_assoc($prodi)) { ?>
                                <option value="<?php echo $p['kd_prodi']; ?>">
                                    <?php echo $p['nama_prodi']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Jenis Kelamin</td>
                    <td>
                        <input type="radio" name="jenis_kelamin" value="L" required> Laki-laki
                        <input type="radio" name="jenis_kelamin" value="P" required> Perempuan
                    </td>
                </tr>
                <tr>
                    <td>Foto (Opsional)</td>
                    <td><input type="file" name="foto"></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button type="submit" name="simpan" class="submit">SIMPAN</button>
                        <button type="button" class="cancel"><a href="siswa.php" style="color:white; 
                        text-decoration:none;">BATAL</a></button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</body>
</html>