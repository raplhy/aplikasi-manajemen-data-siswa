<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: index.php?p=Silahkan login terlebih dahulu!");
    exit();
}
include "koneksi.php";

$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
if ($cari !== '') {
    $stmt = mysqli_prepare($koneksi, "SELECT s.*, p.nama_prodi FROM siswa s JOIN prodi p ON s.kd_prodi = p.kd_prodi WHERE s.nama LIKE ? OR s.nis LIKE ?");
    $param = "%" . $cari . "%";
    mysqli_stmt_bind_param($stmt, "ss", $param, $param);
    mysqli_stmt_execute($stmt);
    $data = mysqli_stmt_get_result($stmt);
} else {
    $data = mysqli_query($koneksi, "SELECT s.*, p.nama_prodi FROM siswa s JOIN prodi p ON s.kd_prodi = p.kd_prodi");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Data Siswa</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>
    <?php include "navigasi.php"; ?>
    <div id="main">
        <div class="container">
            <h2>Data Siswa</h2>
            <hr>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <a href="tambah_siswa.php" class="tambah">TAMBAH DATA SISWA</a>
                <form method="GET" action="siswa.php">
                    <input type="text" name="cari" placeholder="Cari NIS atau Nama..." value="<?php echo $cari; ?>"
                    style="padding: 5px;">
                    <button type="submit" style="padding: 5px;">Cari</button>
                    <?php if($cari != '') echo '<a href="siswa.php" style="padding: 5px; 
                    background: #ccc; text-decoration: none; 
                    color: black; border: 1px solid #999;">Reset</a>'; ?>
                </form>
            </div>
            <table>
                <tr>
                    <th>Foto</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Tahun Ajaran</th>
                    <th>Prodi</th>
                    <th>Jenis Kelamin</th>
                    <th>ACTION</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($data)) { ?>
                    <tr>
                        <td><img src="uploads/<?php echo $row['foto']; ?>
                        " width="50" height="50" style="object-fit: cover;"></td>
                        <td><?php echo $row['nis']; ?></td>
                        <td><?php echo $row['nama']; ?></td>
                        <td><?php echo $row['kelas']; ?></td>
                        <td><?php echo $row['tahun_ajaran']; ?></td>
                        <td><?php echo $row['nama_prodi']; ?></td>
                        <td><?php echo ($row['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></td>
                        <td>
                            <a href="edit_siswa.php?id=<?php echo $row['id']; ?>">EDIT</a>
                            <a href="hapus_siswa.php?id=<?php echo $row['id']; ?>" 
                            onclick="return confirm('Yakin ingin hapus?')">DELETE</a>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</body>
</html>