<?php
checkLogin();
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$id = $_GET['id'];

/* header transfer */
$transfer = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT 
st.*,
w1.name as from_warehouse,
w2.name as to_warehouse
FROM stock_transfers st
JOIN warehouses w1 ON st.from_warehouse_id=w1.id
JOIN warehouses w2 ON st.to_warehouse_id=w2.id
WHERE st.id='$id'
"));

/* detail item */
$details = mysqli_query($conn,"
SELECT 
d.*,
i.item_code,
i.item_name
FROM stock_transfer_details d
JOIN items i ON d.item_id=i.id
WHERE d.transfer_id='$id'
");
?>

<h2>Detail Transfer Stok</h2>

<div class="card">

<p><b>Nomor Transfer :</b> <?= $transfer['transfer_number'] ?></p>
<p><b>Dari Gudang :</b> <?= $transfer['from_warehouse'] ?></p>
<p><b>Ke Gudang :</b> <?= $transfer['to_warehouse'] ?></p>
<p><b>Tanggal :</b> <?= $transfer['transfer_date'] ?></p>

<br>

<table border="1" width="100%" cellpadding="8">
<tr>
<th>No</th>
<th>Kode Item</th>
<th>Nama Item</th>
<th>Qty</th>
<th>CBM</th>
</tr>

<?php
$no=1;
while($d=mysqli_fetch_assoc($details)){
?>

<tr>
<td><?= $no++ ?></td>
<td><?= $d['item_code'] ?></td>
<td><?= $d['item_name'] ?></td>
<td><?= $d['qty'] ?></td>
<td><?= $d['cbm'] ?></td>
</tr>

<?php } ?>

</table>

<br>

<a href="index.php?module=stock_transfers&action=index" class="btn btn-primary">
Kembali
</a>

</div>