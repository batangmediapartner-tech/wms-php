<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$data = mysqli_query($conn,"
SELECT * FROM warehouses
WHERE is_deleted=0
ORDER BY id DESC
");
?>

<h2>Master Gudang</h2>

<div class="card">

<a class="btn btn-success"
href="index.php?module=warehouses&action=create">
+ Tambah Gudang
</a>

<br><br>

<table>
<tr>
<th>No</th>
<th>Nama</th>
<th>Kapasitas (CBM)</th>
<th>Status</th>
<th>Aksi</th>
</tr>

<?php $no=1; while($row=mysqli_fetch_assoc($data)){ ?>
<tr>
<td><?= $no++; ?></td>
<td><?= $row['name']; ?></td>
<td><?= number_format($row['capacity_cbm'],0); ?></td>
<td>
<?php if($row['is_active']){ ?>
<span style="color:green;font-weight:bold;">ACTIVE</span>
<?php } else { ?>
<span style="color:red;font-weight:bold;">NON ACTIVE</span>
<?php } ?>
</td>
<td>
<a class="btn btn-warning"
href="index.php?module=warehouses&action=edit&id=<?= $row['id']; ?>">
Edit
</a>
</td>
</tr>
<?php } ?>

</table>

</div>

</div></body></html>