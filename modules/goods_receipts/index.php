<?php
checkLogin();
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$limitOptions = [10,25,50,100];
$limit = $_GET['limit'] ?? 10;
if(!in_array($limit,$limitOptions)) $limit = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page-1)*$limit;

$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$where = "WHERE 1=1";

if($search!=''){
    $where .= " AND gr_number LIKE '%$search%'";
}
if($date_from!=''){
    $where .= " AND DATE(created_at) >= '$date_from'";
}
if($date_to!=''){
    $where .= " AND DATE(created_at) <= '$date_to'";
}

/* TOTAL */
$totalQuery = mysqli_query($conn,"SELECT COUNT(*) as total FROM goods_receipts $where");
$totalData = mysqli_fetch_assoc($totalQuery)['total'];
$totalPages = ceil($totalData/$limit);

/* DATA */
$query = mysqli_query($conn,"
SELECT * FROM goods_receipts
$where
ORDER BY created_at DESC
LIMIT $limit OFFSET $offset
");
?>

<h2>Goods Receipt</h2>

<div class="card">

<form method="GET" id="filterForm"
style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">

<input type="hidden" name="module" value="goods_receipts">
<input type="hidden" name="action" value="index">

<input type="text" name="search" id="searchInput"
placeholder="Cari Nomor GR..."
value="<?= $search ?>">

<input type="date" name="date_from" value="<?= $date_from ?>">
<input type="date" name="date_to" value="<?= $date_to ?>">

<select name="limit">
<?php foreach($limitOptions as $l){ ?>
<option value="<?= $l ?>" <?= $limit==$l?'selected':'' ?>>
<?= $l ?> rows
</option>
<?php } ?>
</select>

<button class="btn btn-primary">Filter</button>

<a href="index.php?module=goods_receipts&action=create"
class="btn btn-success">+ Buat GR</a>

</form>

<br>

<table>
<tr>
<th>No</th>
<th>Nomor GR</th>
<th>Tanggal</th>
<th>Status</th>
<th>Aksi</th>
</tr>

<?php
$no=$offset+1;
while($row=mysqli_fetch_assoc($query)){
?>
<tr>
<td><?= $no++; ?></td>
<td><?= $row['gr_number']; ?></td>
<td><?= $row['created_at']; ?></td>
<td><?= $row['status']; ?></td>
<td>
<a href="index.php?module=goods_receipts&action=detail&id=<?= $row['id']; ?>" <button class="btn btn-primary">Detail</button>

<a href="index.php?module=goods_receipts&action=void&id=<?= $row['id']; ?>"<button class="btn btn-success">Void</button>
</td>
</tr>
<?php } ?>
</table>

<br>

<?php if($totalPages > 1){ ?>

<?php
$range = 2;
$start = max(1, $page - $range);
$end   = min($totalPages, $page + $range);

$buildUrl = function($p) use ($search,$date_from,$date_to,$limit){
    return "index.php?module=goods_receipts&action=index"
        ."&page=".$p
        ."&search=".$search
        ."&date_from=".$date_from
        ."&date_to=".$date_to
        ."&limit=".$limit;
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

<script>
document.getElementById("searchInput").addEventListener("keyup", function(){
    clearTimeout(window.searchTimer);
    window.searchTimer = setTimeout(function(){
        document.getElementById("filterForm").submit();
    }, 500);
});
</script>

</div></body></html>