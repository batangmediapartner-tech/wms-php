<?php
checkLogin();
global $conn;

$sj=$_POST['sj_number'];
$tanggal=$_POST['tanggal'];
$customer=$_POST['customer_id'];
$warehouse=$_POST['warehouse_id'];

mysqli_query($conn,"
INSERT INTO surat_jalan
(sj_number,warehouse_id,customer_id,tanggal,created_by)
VALUES
('$sj','$warehouse','$customer','$tanggal','".$_SESSION['user_id']."')
");

$id=mysqli_insert_id($conn);

foreach($_POST['model'] as $k=>$m){

$qty=$_POST['qty'][$k];
$do=$_POST['no_do'][$k];
$po=$_POST['no_po'][$k];

if(!$m || $qty<=0) continue;

mysqli_query($conn,"
INSERT INTO surat_jalan_details
(sj_id,model,qty,no_do,no_po)
VALUES
('$id','$m','$qty','$do','$po')
");

}

header("location:index.php");