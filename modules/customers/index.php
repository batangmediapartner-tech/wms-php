<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$data = mysqli_query($conn,"SELECT * FROM customers WHERE is_active=1 ORDER BY id DESC");
?>

<h2>Master Customer</h2>

<a class="btn btn-primary" href="index.php?module=customers&action=create">
+ Tambah Customer
</a>

<br><br>

<table>
<tr>
<th>No</th>
<th>Kode</th>
<th>Nama</th>
<th>Telepon</th>
<th>Aksi</th>
</tr>

<?php $no=1; while($row=mysqli_fetch_assoc($data)){ ?>
<tr>
<td><?= $no++; ?></td>
<td><?= $row['customer_code']; ?></td>
<td><?= $row['customer_name']; ?></td>
<td><?= $row['phone']; ?></td>
<td>
<a class="btn btn-warning"
href="index.php?module=customers&action=edit&id=<?= $row['id']; ?>">
Edit
</a>
</td>
</tr>
<?php } ?>
</table>

</div></body></html>