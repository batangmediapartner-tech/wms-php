<?php

checkLogin();
checkRole(['ADMIN','WAREHOUSE']);
global $conn;

$action = $_GET['action'] ?? '';

if($action == 'create'){
    require 'create.php';
    exit;
}

if($action == 'import'){
    require 'import_items.php';
    exit;
}

if($action == 'reset_import'){
    require 'reset_import.php';
    exit;
}

if($action == 'print'){
    require 'print.php';
    exit;
}

require BASE_PATH."/modules/layout/sidebar.php";

$q=mysqli_query($conn,"
SELECT sj.*,c.customer_name,w.name warehouse
FROM surat_jalan sj
LEFT JOIN customers c ON sj.customer_id=c.id
LEFT JOIN warehouses w ON sj.warehouse_id=w.id
ORDER BY sj.id DESC
");

?>

<h2>Surat Jalan</h2>

<a href="?module=surat_jalan&action=create" class="btn btn-primary">
Buat Surat Jalan
</a>

<table class="table table-bordered">

<tr>
<th>No SJ</th>
<th>Tanggal</th>
<th>Customer</th>
<th>Gudang</th>
<th>Aksi</th>
</tr>

<?php while($d=mysqli_fetch_assoc($q)){ ?>

<tr>

<td><?=$d['sj_number']?></td>
<td><?=$d['tanggal']?></td>
<td><?=$d['customer_name']?></td>
<td><?=$d['warehouse']?></td>

<td>

<a href="?module=surat_jalan&action=print&id=<?=$d['id']?>">Print</a>

</td>

</tr>

<?php } ?>

</table>

<?php require BASE_PATH."/modules/layout/footer.php"; ?>