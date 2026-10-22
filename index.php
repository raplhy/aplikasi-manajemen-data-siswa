<?php
session_start();

$_SESSION['login'] = false;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login Sistem Manajemen Data Siswa</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h2>PANEL LOGIN</h2>
        <hr>
        <form action="cek_login.php" method="POST">
            <div class="form-control">
                <input type="text" name="user" placeholder="Masukan username">
            </div>
            <div class="form-control">
                <input type="password" name="pass" placeholder="Masukan password">
            </div>
            <div class="form-control">
                <button type="submit">LOGIN</button>
            </div>
        </form>
    </div>
</body>
</html>