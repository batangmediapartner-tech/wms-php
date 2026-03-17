<?php
checkLogin();
global $conn;

$id = $_GET['id'];

$gr = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT gr.*, w.name as warehouse_name, m.manifest_number
    FROM goods_receipts gr
    JOIN warehouses w ON gr.warehouse_id=w.id
    LEFT JOIN manifests m ON gr.manifest_id=m.id
    WHERE gr.id='$id'
"));

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=GR_".$gr['gr_number'].".xls");

$details = mysqli_query($conn,"
    SELECT d.*, i.item_code, i.item_name
    FROM goods_receipt_details d
    JOIN items i ON d.item_id=i.id
    WHERE d.gr_id='$id'
");

$total_qty=0;
$total_cbm=0;
?>

<table border="1">
<tr><th colspan="5">GOODS RECEIPT</th></tr>
<tr><td>No GR</td><td><?= $gr['gr_number']; ?></td></tr>
<tr><td>Gudang</td><td><?= $gr['warehouse_name']; ?></td></tr>
<tr><td>Manifest</td><td><?= $gr['manifest_number']; ?></td></tr>
<tr><td>Tanggal</td><td><?= date('d-m-Y',strtotime($gr['created_at'])); ?></td></tr>

<tr><td colspan="5"></td></tr>

<tr>
<th>No</th>
<th>Item Code</th>
<th>Item Name</th>
<th>Qty</th>
<th>CBM</th>
</tr>

<?php $no=1; while($row=mysqli_fetch_assoc($details)){ 
$total_qty += $row['qty'];
$total_cbm += $row['cbm'];
?>
<tr>
<td><?= $no++; ?></td>
<td><?= $row['item_code']; ?></td>
<td><?= $row['item_name']; ?></td>
<td><?= $row['qty']; ?></td>
<td><?= number_format($row['cbm'],2); ?></td>
</tr>
<?php } ?>

<tr>
<th colspan="3">TOTAL</th>
<th><?= $total_qty; ?></th>
<th><?= number_format($total_cbm,2); ?></th>
</tr>

</table>