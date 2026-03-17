<?php
checkLogin();
global $conn;

$id=$_GET['id'];

mysqli_query($conn,"DELETE FROM surat_jalan WHERE id='$id'");
mysqli_query($conn,"DELETE FROM surat_jalan_details WHERE sj_id='$id'");

header("location:index.php");