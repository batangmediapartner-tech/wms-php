<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$search     = $_GET['search'] ?? '';
$date_from  = $_GET['date_from'] ?? '';
$date_to    = $_GET['date_to'] ?? '';
$limit      = $_GET['limit'] ?? 25;
$page       = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$allowedLimit = [10,25,50,100];
if(!in_array($limit,$allowedLimit)) $limit = 25;

$offset = ($page-1)*$limit;

$where = "WHERE 1=1";

if($search!=''){
    $where .= " AND (u.username LIKE '%$search%' 
                 OR al.module LIKE '%$search%' 
                 OR al.action LIKE '%$search%')";
}

if($date_from!=''){
    $where .= " AND DATE(al.created_at) >= '$date_from'";
}

if($date_to!=''){
    $where .= " AND DATE(al.created_at) <= '$date_to'";
}

/* TOTAL */
$totalQuery = mysqli_query($conn,"
SELECT COUNT(*) as total
FROM activity_logs al
LEFT JOIN users u ON u.id = al.user_id
$where
");

$totalData  = mysqli_fetch_assoc($totalQuery)['total'];
$totalPages = ceil($totalData/$limit);

/* DATA */
$query = mysqli_query($conn,"
SELECT al.*, u.username
FROM activity_logs al
LEFT JOIN users u ON u.id = al.user_id
$where
ORDER BY al.created_at DESC
LIMIT $limit OFFSET $offset
");
?>

<style>
.card-modern{
background:white;
padding:25px;
border-radius:12px;
box-shadow:0 8px 20px rgba(0,0,0,0.08);
margin-bottom:20px;
}

.table-modern{
width:100%;
border-collapse:collapse;
font-size:13px;
}

.table-modern th{
background:#f8fafc;
padding:10px;
text-align:left;
border-bottom:2px solid #e2e8f0;
}

.table-modern td{
padding:10px;
border-bottom:1px solid #f1f5f9;
}

.badge{
padding:4px 10px;
border-radius:20px;
font-size:11px;
font-weight:bold;
color:white;
}

.badge-create{background:#16a34a;}
.badge-update{background:#2563eb;}
.badge-delete{background:#dc2626;}
.badge-login{background:#f59e0b;}
.badge-logout{background:#64748b;}
.badge-default{background:#94a3b8;}

.filter-bar{
display:flex;
flex-wrap:wrap;
gap:10px;
align-items:center;
margin-bottom:20px;
}

.filter-bar input, .filter-bar select{
padding:6px 10px;
border-radius:6px;
border:1px solid #ddd;
font-size:12px;
}

.pagination{
margin-top:15px;
}

.pagination a{
padding:6px 10px;
margin-right:5px;
background:#e2e8f0;
border-radius:5px;
text-decoration:none;
color:#333;
font-size:12px;
}

.pagination a.active{
background:#2563eb;
color:white;
}
</style>

<h2>Activity Logs</h2>

<div class="card-modern">

<form method="GET" id="filterForm" class="filter-bar">
<input type="hidden" name="module" value="activity_logs">
<input type="hidden" name="action" value="index">

<input type="text" name="search" id="searchInput"
placeholder="Cari user / module / action..."
value="<?= $search ?>">

<input type="date" name="date_from" value="<?= $date_from ?>">
<input type="date" name="date_to" value="<?= $date_to ?>">

<select name="limit">
<?php foreach($allowedLimit as $l){ ?>
<option value="<?= $l ?>" <?= $limit==$l?'selected':'' ?>>
<?= $l ?> rows
</option>
<?php } ?>
</select>

<button type="submit">Filter</button>

</form>

<table class="table-modern">
<tr>
<th>No</th>
<th>User</th>
<th>Module</th>
<th>Action</th>
<th>Description</th>
<th>Date</th>
</tr>

<?php
$no=$offset+1;
while($row=mysqli_fetch_assoc($query)){

$action = strtolower($row['action']);
$badgeClass="badge-default";

if($action=='create') $badgeClass="badge-create";
elseif($action=='update') $badgeClass="badge-update";
elseif($action=='delete') $badgeClass="badge-delete";
elseif($action=='login')  $badgeClass="badge-login";
elseif($action=='logout') $badgeClass="badge-logout";
?>
<tr>
<td><?= $no++; ?></td>
<td><?= htmlspecialchars($row['username'] ?? 'System'); ?></td>
<td><?= htmlspecialchars($row['module']); ?></td>
<td><span class="badge <?= $badgeClass ?>">
<?= strtoupper($row['action']); ?>
</span></td>
<td><?= htmlspecialchars($row['description']); ?></td>
<td><?= $row['created_at']; ?></td>
</tr>
<?php } ?>
</table>

<div class="pagination">
<?php for($i=1;$i<=$totalPages;$i++){ ?>
<a class="<?= $i==$page?'active':'' ?>"
href="index.php?module=activity_logs&action=index&page=<?= $i ?>&search=<?= $search ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>&limit=<?= $limit ?>">
<?= $i ?>
</a>
<?php } ?>
</div>

</div>

<script>
document.getElementById("searchInput").addEventListener("keyup", function(){
    clearTimeout(window.searchTimer);
    window.searchTimer = setTimeout(function(){
        document.getElementById("filterForm").submit();
    }, 400);
});
</script>

</div></body></html>