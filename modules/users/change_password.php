<?php
checkLogin();
global $conn;

$message = "";

if(isset($_POST['change_password'])){

    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $id = $_SESSION['user_id'];

    $q = mysqli_query($conn,"SELECT password FROM users WHERE id='$id'");
    $user = mysqli_fetch_assoc($q);

    if(!password_verify($old, $user['password'])){
        $message = "Password lama salah.";
    }
    elseif(strlen($new) < 8){
        $message = "Password minimal 8 karakter.";
    }
    elseif($new !== $confirm){
        $message = "Konfirmasi tidak cocok.";
    }
    else{
        $hash = password_hash($new, PASSWORD_DEFAULT);

        mysqli_query($conn,"
            UPDATE users SET password='$hash' WHERE id='$id'
        ");

        logActivity('User','CHANGE PASSWORD','User ganti password');

        $message = "Password berhasil diganti.";
    }
}
?>

<h2>Ganti Password</h2>

<a href="index.php?module=dashboard&action=index">Kembali</a>

<p><?= $message; ?></p>

<form method="POST">
Password Lama:<br>
<input type="password" name="old_password" required><br><br>

Password Baru:<br>
<input type="password" name="new_password" required><br><br>

Confirm Password:<br>
<input type="password" name="confirm_password" required><br><br>

<button type="submit" name="change_password">Ganti Password</button>
</form>