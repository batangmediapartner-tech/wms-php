<?php
checkLogin();
checkRole(['ADMIN']);

global $conn;

$id = $_GET['id'];

mysqli_query($conn, "
    UPDATE items 
    SET is_deleted = 1, deleted_at = NOW() 
    WHERE id = '$id'
");

header("Location: index.php?module=items&action=index");
exit;
?>