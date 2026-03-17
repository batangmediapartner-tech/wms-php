<?php
checkLogin();
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$warehouse_id = $_GET['warehouse_id'] ?? '';
$search       = $_GET['search'] ?? '';
$date_from    = $_GET['date_from'] ?? '';
$date_to      = $_GET['date_to'] ?? '';
$sort         = $_GET['sort'] ?? 'item_code';
$order        = $_GET['order'] ?? 'ASC';
$page         = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;

$limit  = 10;
$offset = ($page - 1) * $limit;

/* ========== VALID SORT ========== */
$allowedSort = ['warehouse_name','item_code','item_name','qty_available','total_cbm'];
if(!in_array($sort,$allowedSort)) $sort='item_code';
$order = strtoupper($order)=='DESC'?'DESC':'ASC';

$where = "WHERE 1=1";

if($warehouse_id!=''){
    $where .= " AND s.warehouse_id='$warehouse_id'";
}

if($search!=''){
    $where .= " AND (i.item_code LIKE '%$search%' OR i.item_name LIKE '%$search%')";
}

if($date_from!=''){
    $where .= " AND DATE(s.updated_at) >= '$date_from'";
}

if($date_to!=''){
    $where .= " AND DATE(s.updated_at) <= '$date_to'";
}

/* ========== TOTAL ========== */
$totalQuery = mysqli_query($conn,"
SELECT COUNT(*) as total
FROM stocks s
JOIN items i ON s.item_id=i.id
JOIN warehouses w ON s.warehouse_id=w.id
$where
");

$totalData  = mysqli_fetch_assoc($totalQuery)['total'];
$totalPages = ceil($totalData / $limit);

if($page > $totalPages && $totalPages > 0){
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

/* ========== DATA ========== */
$query = mysqli_query($conn,"
SELECT w.name as warehouse_name,
       i.item_code,
       i.item_name,
       s.qty_available,
       s.total_cbm
FROM stocks s
JOIN items i ON s.item_id=i.id
JOIN warehouses w ON s.warehouse_id=w.id
$where
ORDER BY $sort $order
LIMIT $limit OFFSET $offset
");

$warehouses = mysqli_query($conn,"SELECT * FROM warehouses WHERE is_deleted=0");

function sortLink($column,$label){
    $currentSort = $_GET['sort'] ?? '';
    $currentOrder = $_GET['order'] ?? 'ASC';
    $newOrder = ($currentSort==$column && $currentOrder=='ASC')?'DESC':'ASC';
    $query = $_GET;
    $query['sort']=$column;
    $query['order']=$newOrder;
    unset($query['page']);
    $queryStr=http_build_query($query);
    return "<a href='index.php?$queryStr'>$label</a>";
}
?>

<h2>Laporan Stok</h2>

<div class="card">

<form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
<input type="hidden" name="module" value="reports">
<input type="hidden" name="action" value="stock">

<select name="warehouse_id">
<option value="">-- Semua Gudang --</option>
<?php while($w=mysqli_fetch_assoc($warehouses)){ ?>
<option value="<?= $w['id']; ?>" <?= $warehouse_id==$w['id']?'selected':'' ?>>
<?= $w['name']; ?>
</option>
<?php } ?>
</select>

<input type="date" name="date_from" value="<?= $date_from ?>">
<input type="date" name="date_to" value="<?= $date_to ?>">

<input type="text" name="search" placeholder="Cari kode / nama..." value="<?= $search ?>">

<button class="btn btn-primary">Filter</button>

<a class="btn btn-success"
href="index.php?module=reports&action=export_stock&warehouse_id=<?= $warehouse_id ?>&search=<?= $search ?>">
Export Excel
</a>

</form>

<br>

<table>
<tr>
<th><?= sortLink('warehouse_name','Gudang') ?></th>
<th><?= sortLink('item_code','Kode') ?></th>
<th><?= sortLink('item_name','Nama') ?></th>
<th><?= sortLink('qty_available','Qty') ?></th>
<th><?= sortLink('total_cbm','CBM') ?></th>
</tr>

<?php while($row=mysqli_fetch_assoc($query)){ ?>
<tr>
<td><?= $row['warehouse_name'] ?></td>
<td><?= $row['item_code'] ?></td>
<td><?= $row['item_name'] ?></td>
<td><?= number_format($row['qty_available']) ?></td>
<td><?= number_format($row['total_cbm'],0) ?></td>
</tr>
<?php } ?>
</table>

<br>

<?php if($totalPages > 1){ ?>

<?php
$params = $_GET;
unset($params['page']);

$range = 2; // jumlah halaman kiri kanan
$start = max(1, $page - $range);
$end   = min($totalPages, $page + $range);

$buildUrl = function($p) use ($params){
    $params['page'] = $p;
    return "index.php?" . http_build_query($params);
};
?>

<div style="display:flex;gap:5px;flex-wrap:wrap;align-items:center;">

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