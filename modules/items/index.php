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
    $where .= " AND (item_name LIKE '%$search%' OR item_code LIKE '%$search%')";
}

$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM items $where");
$totalData = mysqli_fetch_assoc($totalQuery)['total'];
$totalPages = ceil($totalData / $limit);

$query = mysqli_query($conn, "
    SELECT * FROM items 
    $where 
    ORDER BY id DESC 
    LIMIT $limit OFFSET $offset
");
?>

<h2>Master Item</h2>

<div class="card">

<form method="GET" style="display:flex;gap:10px;align-items:center;">
    <input type="hidden" name="module" value="items">
    <input type="hidden" name="action" value="index">

    <input type="text" name="search" 
           placeholder="Cari kode / nama item..."
           value="<?= $search; ?>">

    <button class="btn btn-primary">Search</button>

    <a class="btn btn-success"
       href="index.php?module=items&action=create">
       + Tambah Item
    </a>

    <a class="btn btn-warning"
       href="index.php?module=items&action=import">
       Import CSV
    </a>
</form>

<br>

<table>
<tr>
<th>No</th>
<th>Kode</th>
<th>Nama</th>
<th>CBM</th>
<th>Status</th>
<th>Aksi</th>
</tr>

<?php $no = $offset + 1; ?>
<?php while ($row = mysqli_fetch_assoc($query)) { ?>
<tr>
<td><?= $no++; ?></td>
<td><b><?= $row['item_code']; ?></b></td>
<td><?= $row['item_name']; ?></td>
<td><?= number_format($row['cbm'],6); ?></td>
<td>
<?php if($row['is_active']){ ?>
<span style="color:green;font-weight:bold;">ACTIVE</span>
<?php } else { ?>
<span style="color:red;font-weight:bold;">NON ACTIVE</span>
<?php } ?>
</td>
<td>
<a class="btn btn-warning"
href="index.php?module=items&action=edit&id=<?= $row['id']; ?>">
Edit
</a>

<a class="btn btn-danger"
href="index.php?module=items&action=delete&id=<?= $row['id']; ?>"
onclick="return confirm('Yakin hapus item ini?')">
Delete
</a>
</td>
</tr>
<?php } ?>
</table>

<br>

<?php if($totalPages > 1){ ?>

<?php
$range = 2; // jumlah halaman kiri kanan
$start = max(1, $page - $range);
$end   = min($totalPages, $page + $range);

$buildUrl = function($p) use ($search){
    return "index.php?module=items&action=index&page=".$p."&search=".$search;
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