<?php
checkLogin();
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$warehouse_id = $_GET['warehouse_id'] ?? '';
$customer_id  = $_GET['customer_id'] ?? '';
$search       = $_GET['search'] ?? '';
$limit        = $_GET['limit'] ?? 10;
$page         = isset($_GET['page']) ? (int)$_GET['page'] : 1;

/* ===== TAMBAHAN FILTER TANGGAL ===== */
$date_from    = $_GET['date_from'] ?? '';
$date_to      = $_GET['date_to'] ?? '';

$allowedLimit = [10,25,50,100];
if(!in_array($limit,$allowedLimit)) $limit = 10;

$offset = ($page - 1) * $limit;

$where = "WHERE 1=1";

if($warehouse_id!=''){
    $where .= " AND sc.warehouse_id='$warehouse_id'";
}

if($customer_id!=''){
    $where .= " AND d.customer_id='$customer_id'";
}

if($search!=''){
    $where .= " AND (i.item_code LIKE '%$search%' OR sc.reference_number LIKE '%$search%')";
}

/* ===== FILTER TANGGAL (AMAN) ===== */
if($date_from!='' && $date_to!=''){
    $where .= " AND DATE(sc.created_at) BETWEEN '$date_from' AND '$date_to'";
}elseif($date_from!=''){
    $where .= " AND DATE(sc.created_at) >= '$date_from'";
}elseif($date_to!=''){
    $where .= " AND DATE(sc.created_at) <= '$date_to'";
}


/* ================= TOTAL ================= */
$totalQuery=mysqli_query($conn,"
SELECT COUNT(*) as total
FROM stock_cards sc
JOIN items i ON sc.item_id=i.id
JOIN warehouses w ON sc.warehouse_id=w.id
LEFT JOIN delivery_orders d ON sc.reference_number=d.do_number
$where
");

$totalData=mysqli_fetch_assoc($totalQuery)['total'];
$totalPages=ceil($totalData/$limit);

/* ================= DATA ================= */
$query=mysqli_query($conn,"
SELECT 
    sc.*,
    w.name as warehouse_name,
    i.item_code,
    d.customer_id,
    c.customer_name
FROM stock_cards sc
JOIN items i ON sc.item_id=i.id
JOIN warehouses w ON sc.warehouse_id=w.id
LEFT JOIN delivery_orders d ON sc.reference_number=d.do_number
LEFT JOIN customers c ON d.customer_id=c.id
$where
ORDER BY sc.created_at DESC
LIMIT $limit OFFSET $offset
");

/* FIX: tanpa is_deleted */
$warehouses=mysqli_query($conn,"SELECT * FROM warehouses");
$customers=mysqli_query($conn,"SELECT * FROM customers");
?>

<h2>Kartu Stok</h2>

<div class="card">

<form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
<input type="hidden" name="module" value="reports">
<input type="hidden" name="action" value="stock_card">

<select name="warehouse_id">
<option value="">-- Semua Gudang --</option>
<?php while($w=mysqli_fetch_assoc($warehouses)){ ?>
<option value="<?= $w['id'] ?>" <?= $warehouse_id==$w['id']?'selected':'' ?>>
<?= $w['name'] ?>
</option>
<?php } ?>
</select>

<select name="customer_id">
<option value="">-- Semua Customer --</option>
<?php while($c=mysqli_fetch_assoc($customers)){ ?>
<option value="<?= $c['id'] ?>" <?= $customer_id==$c['id']?'selected':'' ?>>
<?= $c['customer_name'] ?>
</option>
<?php } ?>
</select>

<!-- ✅ FILTER TANGGAL -->
<input type="date" name="date_from" value="<?= $date_from ?>">
<input type="date" name="date_to" value="<?= $date_to ?>">

<input type="text" name="search"
placeholder="Cari kode / reference..."
value="<?= $search ?>">

<select name="limit">
<?php foreach($allowedLimit as $l){ ?>
<option value="<?= $l ?>" <?= $limit==$l?'selected':'' ?>>
<?= $l ?> rows
</option>
<?php } ?>
</select>

<button class="btn btn-primary">Filter</button>

<a class="btn btn-success"
href="index.php?module=reports&action=export_stock_card&warehouse_id=<?= $warehouse_id ?>&customer_id=<?= $customer_id ?>&search=<?= $search ?>">
Export Excel
</a>

</form>

<br>

<table>
<tr>
<th>No</th>
<th>Gudang</th>
<th>Tanggal</th>
<th>Kode</th>
<th>Type</th>
<th>Reference</th>
<th>Qty In</th>
<th>Qty Out</th>
<th>Balance</th>
</tr>

<?php 
$no=$offset+1;
while($row=mysqli_fetch_assoc($query)){ 

$reference_display = $row['reference_number'];

if($row['transaction_type']=='DO' && !empty($row['customer_name'])){
    $reference_display .= " - ".$row['customer_name'];
}
?>

<tr>
<td><?= $no++; ?></td>
<td><?= $row['warehouse_name']; ?></td>
<td><?= $row['created_at']; ?></td>
<td><?= $row['item_code']; ?></td>
<td><?= $row['transaction_type']; ?></td>
<td><?= $reference_display; ?></td>
<td><?= $row['qty_in']; ?></td>
<td><?= $row['qty_out']; ?></td>
<td><?= $row['balance_after']; ?></td>
</tr>
<?php } ?>
</table>

<br>

<?php if($totalPages>1){ ?>

<?php
$range = 2; // jumlah halaman di kiri kanan page aktif
$start = max(1, $page - $range);
$end   = min($totalPages, $page + $range);

$baseUrl = "index.php?module=reports&action=stock_card"
    ."&warehouse_id=$warehouse_id"
    ."&customer_id=$customer_id"
    ."&search=$search"
    ."&limit=$limit"
    ."&date_from=$date_from"
    ."&date_to=$date_to";
?>

<div style="display:flex;gap:5px;flex-wrap:wrap;align-items:center;">

<!-- Prev -->
<?php if($page>1){ ?>
<a class="btn"
href="<?= $baseUrl ?>&page=<?= $page-1 ?>">Prev</a>
<?php } ?>

<!-- First -->
<?php if($start>1){ ?>
<a class="btn" href="<?= $baseUrl ?>&page=1">1</a>
<?php if($start>2){ ?><span>...</span><?php } ?>
<?php } ?>

<!-- Middle Pages -->
<?php for($i=$start;$i<=$end;$i++){ ?>
<a class="btn <?= $i==$page?'btn-primary':'' ?>"
href="<?= $baseUrl ?>&page=<?= $i ?>">
<?= $i ?>
</a>
<?php } ?>

<!-- Last -->
<?php if($end<$totalPages){ ?>
<?php if($end<$totalPages-1){ ?><span>...</span><?php } ?>
<a class="btn" href="<?= $baseUrl ?>&page=<?= $totalPages ?>">
<?= $totalPages ?>
</a>
<?php } ?>

<!-- Next -->
<?php if($page<$totalPages){ ?>
<a class="btn"
href="<?= $baseUrl ?>&page=<?= $page+1 ?>">Next</a>
<?php } ?>

</div>

<?php } ?>

</div>

</div></body></html>