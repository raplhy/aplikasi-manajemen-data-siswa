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
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM prodi WHERE nama_prodi LIKE ? OR kd_prodi LIKE ?");
    $param = "%" . $cari . "%";
    mysqli_stmt_bind_param($stmt, "ss", $param, $param);
    mysqli_stmt_execute($stmt);
    $data = mysqli_stmt_get_result($stmt);
} else {
    $data = mysqli_query($koneksi, "SELECT * FROM prodi");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Data Prodi</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>
    <?php include "navigasi.php"; ?>
    <div id="main">
        <div class="container">
            <h2>Data Prodi</h2>
            <hr>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <a href="tambah_prodi.php" class="tambah">TAMBAH DATA PRODI</a>
                <form method="GET" action="prodi.php">
                    <input type="text" name="cari" placeholder="Cari Kode atau Nama Prodi..."
                    value="<?php echo $cari; ?>" style="padding: 5px;">
                    <button type="submit" style="padding: 5px;">Cari</button>
                    <?php if($cari != '') echo '<a href="prodi.php" style="padding: 5px; 
                    background: #ccc; text-decoration: none; 
                    color: black; border: 1px solid #999;">Reset</a>'; ?>
                </form>
            </div>
            <table>
                <tr>
                    <th>Kode Prodi</th>
                    <th>Nama Prodi</th>
                    <th>ACTION</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($data)) { ?>
                    <tr>
                        <td><?php echo $row['kd_prodi']; ?></td>
                        <td><?php echo $row['nama_prodi']; ?></td>
                        <td>
                            <a href="edit_prodi.php?id_prodi=<?php echo $row['id_prodi']; ?>">EDIT</a>
                            <a href="hapus_prodi.php?id_prodi=<?php echo $row['id_prodi']; ?>" 
                            onclick="return confirm('Yakin ingin hapus?')">DELETE</a>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</body>
</html>