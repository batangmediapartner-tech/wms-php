<?php
checkLogin();
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$q=mysqli_query($conn,"
SELECT t.*,wf.name as from_wh,wt.name as to_wh
FROM stock_transfers t
JOIN warehouses wf ON wf.id=t.from_warehouse_id
JOIN warehouses wt ON wt.id=t.to_warehouse_id
ORDER BY t.id DESC
");
?>

<h2>Transfer Stok Gudang</h2>

<a class="btn btn-primary"
href="index.php?module=stock_transfers&action=create">
Buat Transfer
</a>

<br><br>

<table>
<tr>
<th>No</th>
<th>Nomor</th>
<th>Dari</th>
<th>Ke</th>
<th>Tanggal</th>
<th>Status</th>
<th>Aksi</th>
</tr>

<?php
$no=1;
while($r=mysqli_fetch_assoc($q)){
?>

<tr>

<td><?= $no++ ?></td>
<td><?= $r['transfer_number'] ?></td>
<td><?= $r['from_wh'] ?></td>
<td><?= $r['to_wh'] ?></td>
<td><?= $r['transfer_date'] ?></td>
<td><?= $r['status'] ?? 'POSTED' ?></td>

<td>

<a href="index.php?module=stock_transfers&action=detail&id=<?= $r['id'] ?>">
Detail
</a>

<?php if(($r['status'] ?? 'POSTED') == 'POSTED'){ ?>

|
<a onclick="return confirm('Void transfer?')"
href="index.php?module=stock_transfers&action=void&id=<?= $r['id'] ?>">
Void
</a>

<?php } ?>

</td>

</tr>

<?php } ?>

</table>

</div></body></html>