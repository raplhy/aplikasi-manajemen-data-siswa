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

$stmt = mysqli_prepare($koneksi, "SELECT * FROM siswa WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: siswa.php?p=Data siswa tidak ditemukan!");
    exit();
}

$prodi = mysqli_query($koneksi, "SELECT * FROM prodi");
$error = "";

if (isset($_POST['update'])) {
    $nis = trim($_POST['nis'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $tahun_ajaran = trim($_POST['tahun_ajaran'] ?? '');
    $kd_prodi = trim($_POST['kd_prodi'] ?? '');
    $jk = trim($_POST['jenis_kelamin'] ?? '');
    
    if (empty($nis) || empty($nama) || empty($kelas) || empty($tahun_ajaran) || empty($kd_prodi) || empty($jk)) {
        $error = "Semua field wajib diisi!";
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

                if (!empty($data['foto']) && file_exists("uploads/" . $data['foto'])) {
                    unlink("uploads/" . $data['foto']);
                }

                if (move_uploaded_file($tmp, $path)) {
                    $stmt_up = mysqli_prepare($koneksi, "UPDATE siswa SET nis = ?, nama = ?, kelas = ?, tahun_ajaran = ?, kd_prodi = ?, jenis_kelamin = ?, foto = ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt_up, "sssssssi", $nis, $nama, $kelas, $tahun_ajaran, $kd_prodi, $jk, $nama_foto, $id);
                    mysqli_stmt_execute($stmt_up);
                } else {
                    $error = "Gagal mengupload foto!";
                }
            }
        } else {
            $stmt_up = mysqli_prepare($koneksi, "UPDATE siswa SET nis = ?, nama = ?, kelas = ?, tahun_ajaran = ?, kd_prodi = ?, jenis_kelamin = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt_up, "ssssssi", $nis, $nama, $kelas, $tahun_ajaran, $kd_prodi, $jk, $id);
            mysqli_stmt_execute($stmt_up);
        }

        if ($error === "") {
            echo "<h1>Data berhasil diupdate!</h1>";
            echo "<a href='siswa.php' class='batal'>Kembali</a>";
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Data Siswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "navigasi.php"; ?>
    <div id="main">
        <div class="container">
            <h2>EDIT DATA SISWA</h2>
            <hr>
            <form method="POST" enctype="multipart/form-data">
                <table>
                    <tr>
                        <td>NIS</td>
                        <td><input type="text" name="nis" value="<?php echo $data['nis']; ?>" required></td>
                    </tr>
                    <tr>
                        <td>Nama</td>
                        <td><input type="text" name="nama" value="<?php echo $data['nama']; ?>" required></td>
                    </tr>
                    <tr>
                        <td>Kelas</td>
                        <td><input type="text" name="kelas" value="<?php echo $data['kelas']; ?>" required></td>
                    </tr>
                    <tr>
                        <td>Tahun Ajaran</td>
                        <td><input type="text" name="tahun_ajaran" value="<?php echo $data['tahun_ajaran']; ?>" 
                        required></td>
                    </tr>
                    <tr>
                        <td>Jenis Kelamin</td>
                        <td>
                            <input type="radio" name="jenis_kelamin" value="L" 
                            <?php if ($data['jenis_kelamin'] == 'L') echo 'checked'; ?>> Laki-laki
                            <input type="radio" name="jenis_kelamin" value="P" 
                            <?php if ($data['jenis_kelamin'] == 'P') echo 'checked'; ?>> Perempuan
                        </td>
                    </tr>
                    <tr>
                        <td>Program Studi</td>
                        <td>
                            <select name="kd_prodi" required>
                                <option value="">--Pilih Prodi--</option>
                                <?php while ($p = mysqli_fetch_assoc($prodi)) { ?>
                                    <option value="<?php echo $p['kd_prodi']; ?>" 
                                    <?php if ($p['kd_prodi'] == $data['kd_prodi']) echo 'selected'; ?>>
                                        <?php echo $p['nama_prodi']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Foto (Opsional)</td>
                        <td>
                            <img src="uploads/<?php echo $data['foto']; ?>" width="50"><br>
                            <input type="file" name="foto">
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <button type="submit" name="update" class="submit">UPDATE</button>
                            <button type="button" class="cancel"><a href="siswa.php" style="color:white; 
                            text-decoration:none;">BATAL</a></button>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</body>
</html>