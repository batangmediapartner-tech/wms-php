<?php
checkLogin();
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$id = $_GET['id'] ?? 0;

if(!$id){
    die("ID tidak ditemukan");
}

/* ========================
   HEADER DATA
======================== */
$data = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT g.*, 
       w.name AS warehouse_name,
       m.manifest_number
FROM goods_receipts g
LEFT JOIN warehouses w ON g.warehouse_id = w.id
LEFT JOIN manifests m ON g.manifest_id = m.id
WHERE g.id='$id'
"));

if(!$data){
    die("Data GR tidak ditemukan");
}

/* ========================
   DETAIL DATA
======================== */
$details = mysqli_query($conn,"
SELECT gd.*, i.item_code, i.item_name
FROM goods_receipt_details gd
JOIN items i ON gd.item_id = i.id
WHERE gd.gr_id='$id'
");

$totalQty=0;
$totalCbm=0;
?>

<h2>Detail Goods Receipt</h2>

<div class="card">

<p><strong>No GR:</strong> <?= $data['gr_number']; ?></p>
<p><strong>Gudang:</strong> <?= $data['warehouse_name'] ?? '-'; ?></p>
<p><strong>Manifest:</strong> <?= $data['manifest_number'] ?? '-'; ?></p>
<p><strong>Tanggal:</strong> <?= $data['created_at']; ?></p>

<br>

<a href="index.php?module=goods_receipts&action=export_pdf&id=<?= $data['id']; ?>" 
class="btn btn-success" target="_blank">
Export PDF
</a>

</div>

<br>

<table border="1" cellpadding="6" width="100%">
<tr>
<th>No</th>
<th>Item Code</th>
<th>Item Name</th>
<th>Qty</th>
<th>CBM</th>
</tr>

<?php
$no=1;
while($row=mysqli_fetch_assoc($details)){
$totalQty += $row['qty'];
$totalCbm += $row['cbm'];
?>
<tr>
<td><?= $no++; ?></td>
<td><?= $row['item_code']; ?></td>
<td><?= $row['item_name']; ?></td>
<td><?= $row['qty']; ?></td>
<td><?= $row['cbm']; ?></td>
</tr>
<?php } ?>

<tr>
<td colspan="3"><strong>TOTAL</strong></td>
<td><strong><?= $totalQty; ?></strong></td>
<td><strong><?= $totalCbm; ?></strong></td>
</tr>

</table>

<a class="btn btn-primary"
href="index.php?module=goods_receipts&action=index">
Kembali
</a>

</div></body></html>