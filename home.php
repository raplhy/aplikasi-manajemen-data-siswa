<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
if (!isset($_SESSION['login']) || $_SESSION['login'] != true) {
    header("Location : index.php?p=Silahkan login terlebih dahulu!");
    exit();
}
?>

<?php
date_default_timezone_set("Asia/Jakarta");
$jam = date("H"); 

if ($jam >= 4 && $jam < 11) {
    $ucapan = "Selamat Pagi!";
} elseif ($jam >= 11 && $jam < 14) {
    $ucapan = "Selamat Siang!";
} elseif ($jam >= 14 && $jam < 18) {
    $ucapan = "Selamat Sore!";
} else {
    $ucapan = "Selamat Malam!";
}
?>

<!DOCTYPE html>
<html>
 <head>
    <title>Halaman Home</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>
    <?php include "navigasi.php"; ?>
    <div id="main">
        <div class="container">
            <h2>APLIKASI MANAJEMEN DATA SISWA</h2>
            <hr>
            <p>Selamat datang di aplikasi Data Siswa SMKS PGRI 3 Malang</p>
            <p>Tanggal : <?php echo date("d-m-Y"); ?></p>
            <p><?php echo $ucapan; ?></p>
        </div>
    </div>
</body>
</html>