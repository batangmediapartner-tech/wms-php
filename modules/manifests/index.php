<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$search = $_GET['search'] ?? '';
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit  = 10;
$offset = ($page - 1) * $limit;

$where = "WHERE is_deleted = 0";
if ($search != "") {
    $where .= " AND manifest_number LIKE '%$search%'";
}

$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM manifests $where");
$totalData = mysqli_fetch_assoc($totalQuery)['total'];
$totalPages = ceil($totalData / $limit);

$query = mysqli_query($conn, "
    SELECT * FROM manifests
    $where
    ORDER BY id DESC
    LIMIT $limit OFFSET $offset
");
?>

<h2>Master Manifest</h2>

<div class="card">

<form method="GET" style="display:flex;gap:10px;align-items:center;">
    <input type="hidden" name="module" value="manifests">
    <input type="hidden" name="action" value="index">

    <input type="text" name="search"
           placeholder="Cari nomor manifest..."
           value="<?= $search; ?>">

    <button class="btn btn-primary">Search</button>

    <a class="btn btn-success"
       href="index.php?module=manifests&action=create">
       + Tambah Manifest
    </a>
</form>

<br>

<table>
<tr>
<th>No</th>
<th>Nomor Manifest</th>
<th>Tanggal Kedatangan</th>
<th>Status</th>
<th>Aksi</th>
</tr>

<?php $no = $offset + 1; ?>
<?php while ($row = mysqli_fetch_assoc($query)) { ?>
<tr>
<td><?= $no++; ?></td>
<td><b><?= $row['manifest_number']; ?></b></td>
<td><?= date('d-m-Y', strtotime($row['arrival_date'])); ?></td>
<td>
<?php if($row['status']=='OPEN'){ ?>
<span style="color:green;font-weight:bold;">OPEN</span>
<?php } else { ?>
<span style="color:red;font-weight:bold;">CLOSED</span>
<?php } ?>
</td>
<td>
<a class="btn btn-warning"
href="index.php?module=manifests&action=edit&id=<?= $row['id']; ?>">
Edit
</a>

<a class="btn btn-danger"
href="index.php?module=manifests&action=delete&id=<?= $row['id']; ?>"
onclick="return confirm('Yakin hapus manifest ini?')">
Delete
</a>
</td>
</tr>
<?php } ?>
</table>

<br>

<?php if($totalPages > 1){ ?>

<?php
$range = 2; // jumlah halaman kiri kanan current
$start = max(1, $page - $range);
$end   = min($totalPages, $page + $range);

$buildUrl = function($p) use ($search){
    return "index.php?module=manifests&action=index&page=".$p."&search=".$search;
};
?>

<div style="margin-top:15px;display:flex;gap:5px;flex-wrap:wrap;align-items:center;">

<!-- Prev -->
<?php if($page > 1){ ?>
<a class="btn" href="<?= $buildUrl($page-1) ?>">Prev</a>
<?php } ?>

<!-- First -->
<?php if($start > 1){ ?>
<a class="btn" href="<?= $buildUrl(1) ?>">1</a>
<?php if($start > 2){ ?><span>...</span><?php } ?>
<?php } ?>

<!-- Middle -->
<?php for($i=$start;$i<=$end;$i++){ ?>
<a class="btn <?= $i==$page?'btn-primary':'' ?>"
href="<?= $buildUrl($i) ?>">
<?= $i ?>
</a>
<?php } ?>

<!-- Last -->
<?php if($end < $totalPages){ ?>
<?php if($end < $totalPages-1){ ?><span>...</span><?php } ?>
<a class="btn" href="<?= $buildUrl($totalPages) ?>">
<?= $totalPages ?>
</a>
<?php } ?>

<!-- Next -->
<?php if($page < $totalPages){ ?>
<a class="btn" href="<?= $buildUrl($page+1) ?>">Next</a>
<?php } ?>

</div>

<?php } ?>

</div>

</div></body></html>