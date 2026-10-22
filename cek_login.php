<?php
session_start();

include 'koneksi.php';

$username = isset($_POST['user']) ? trim($_POST['user']) : '';
$password = isset($_POST['pass']) ? $_POST['pass'] : '';

if ($username === '' || $password === '') {
    header("Location: index.php?p=Username dan Password wajib diisi!");
    exit();
}

// Gunakan prepared statement untuk mencegah SQL Injection
$stmt = mysqli_prepare($koneksi, "SELECT id, username, password FROM pengguna WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Mendukung verifikasi password hash maupun md5 lama
    $is_valid = false;
    if (password_verify($password, $row['password'])) {
        $is_valid = true;
    } elseif (md5($password) === $row['password']) {
        $is_valid = true;
    }

    if ($is_valid) {
        session_regenerate_id(true);
        $_SESSION['login'] = true;
        $_SESSION['user'] = $row['username'];
        header("Location: home.php");
        exit();
    }
}

header("Location: index.php?p=Username atau Password salah!");
exit();
?>