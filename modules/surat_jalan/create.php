<?php

checkLogin();
checkRole(['ADMIN','WAREHOUSE']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";

/* GENERATE NOMOR SJ */

$today = date('Ymd');

$q = mysqli_query($conn,"
SELECT MAX(sj_number) last_number
FROM surat_jalan
WHERE sj_number LIKE 'SJ-$today-%'
");

$data = mysqli_fetch_assoc($q);

if($data['last_number']){
    $last = explode('-', $data['last_number']);
    $urut = (int)$last[2] + 1;
}else{
    $urut = 1;
}

$urut = str_pad($urut,4,'0',STR_PAD_LEFT);

$number = "SJ-$today-$urut";


/* SAVE */

if(isset($_POST['save'])){

$warehouse_id = $_POST['warehouse_id'];
$customer_id  = $_POST['customer_id'];
$tanggal      = $_POST['tanggal'];

mysqli_query($conn,"
INSERT INTO surat_jalan
(sj_number,warehouse_id,customer_id,tanggal,created_by)
VALUES
('$number','$warehouse_id','$customer_id','$tanggal','".$_SESSION['user_id']."')
");

$sj_id = mysqli_insert_id($conn);

foreach($_POST['model'] as $k=>$model){

$qty   = $_POST['qty'][$k];
$do    = $_POST['no_do'][$k];
$po    = $_POST['no_po'][$k];

if(!$model || $qty<=0) continue;

mysqli_query($conn,"
INSERT INTO surat_jalan_details
(sj_id,model,qty,no_do,no_po)
VALUES
('$sj_id','$model','$qty','$do','$po')
");

}

unset($_SESSION['sj_import']);

echo "<script>location='index.php?module=surat_jalan'</script>";

}

?>

<h2>Buat Surat Jalan</h2>

<form method="POST">

No SJ
<input type="text" value="<?=$number?>" readonly class="form-control">

Tanggal
<input type="date" name="tanggal" required class="form-control">

Customer

<select name="customer_id" class="form-control">

<?php
$c=mysqli_query($conn,"SELECT * FROM customers WHERE deleted_at IS NULL");

while($r=mysqli_fetch_assoc($c)){
echo "<option value='$r[id]'>$r[customer_name]</option>";
}
?>

</select>

Gudang

<select name="warehouse_id" class="form-control">

<?php
$w=mysqli_query($conn,"SELECT * FROM warehouses WHERE is_deleted=0");

while($r=mysqli_fetch_assoc($w)){
echo "<option value='$r[id]'>$r[name]</option>";
}
?>

</select>

<br>

<a href="?module=surat_jalan&action=import" class="btn btn-warning">
Import Excel
</a>

<a href="?module=surat_jalan&action=reset_import" class="btn btn-danger">
Reset Import
</a>

<br><br>

<table class="table" id="items">

<tr>
<th>Model</th>
<th>Qty</th>
<th>No DO</th>
<th>No PO</th>
<th></th>
</tr>

<?php

if(isset($_SESSION['sj_import'])){

foreach($_SESSION['sj_import'] as $row){

?>

<tr>

<td>
<input type="text" name="model[]" value="<?=$row['model']?>" class="form-control">
</td>

<td>
<input type="number" name="qty[]" value="<?=$row['qty']?>" class="form-control">
</td>

<td>
<input type="text" name="no_do[]" value="<?=$row['no_do']?>" class="form-control">
</td>

<td>
<input type="text" name="no_po[]" value="<?=$row['no_po']?>" class="form-control">
</td>

<td></td>

</tr>

<?php }

}else{ ?>

<tr>

<td>

<select name="model[]" class="form-control item-select">

<option value="">Pilih Barang</option>

<?php

$items=mysqli_query($conn,"SELECT name FROM items ORDER BY name");

while($i=mysqli_fetch_assoc($items)){
echo "<option value='$i[name]'>$i[name]</option>";
}

?>

</select>

</td>

<td>
<input type="number" name="qty[]" class="form-control">
</td>

<td>
<input type="text" name="no_do[]" class="form-control">
</td>

<td>
<input type="text" name="no_po[]" class="form-control">
</td>

<td>
<button type="button" onclick="hapus(this)">X</button>
</td>

</tr>

<?php } ?>

</table>

<button type="button" onclick="tambah()" class="btn btn-secondary">
Tambah Barang
</button>

<br><br>

<button name="save" class="btn btn-success">
Simpan
</button>

</form>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet"/>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

<script>

$('.item-select').select2({
width:'100%',
placeholder:'Cari barang'
});

function tambah(){

var table=document.getElementById("items");

var row=table.insertRow();

row.innerHTML=`
<td>
<select name="model[]" class="form-control item-select">

<option value="">Pilih Barang</option>

<?php

$items=mysqli_query($conn,"SELECT name FROM items ORDER BY name");

while($i=mysqli_fetch_assoc($items)){
echo "<option value='$i[name]'>$i[name]</option>";
}

?>

</select>
</td>

<td><input type="number" name="qty[]" class="form-control"></td>

<td><input type="text" name="no_do[]" class="form-control"></td>

<td><input type="text" name="no_po[]" class="form-control"></td>

<td><button type="button" onclick="hapus(this)">X</button></td>
`;

$('.item-select').select2();

}

function hapus(btn){

var row=btn.parentNode.parentNode;

row.parentNode.removeChild(row);

}

</script>

<?php require BASE_PATH."/modules/layout/footer.php"; ?>