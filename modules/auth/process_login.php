<?php
session_start();

/*
Database sudah dipanggil dari public/index.php
Jadi tidak perlu require lagi di sini
*/

global $conn;

$username = $_POST['username'];
$password = $_POST['password'];

$query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($query);

if ($user && password_verify($password, $user['password'])) {

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];

    header("Location: index.php?module=dashboard&action=index");
    exit;

} else {
    echo "Login gagal. <a href='index.php?module=auth&action=login'>Kembali</a>";
}
?>