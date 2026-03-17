<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$search = $_GET['search'] ?? '';
$limit  = $_GET['limit'] ?? 25;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$allowedLimit = [10,25,50,100];
if(!in_array($limit,$allowedLimit)) $limit = 25;

$offset = ($page-1)*$limit;

$where = "WHERE 1=1";
if($search!=''){
    $where .= " AND (username LIKE '%$search%' OR role LIKE '%$search%')";
}

/* TOTAL */
$totalQuery = mysqli_query($conn,"SELECT COUNT(*) as total FROM users $where");
$totalData  = mysqli_fetch_assoc($totalQuery)['total'];
$totalPages = ceil($totalData/$limit);

/* DATA */
$query = mysqli_query($conn,"
SELECT * FROM users
$where
ORDER BY id DESC
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

.table-modern tr:hover{
background:#f9fafb;
}

.badge{
padding:4px 10px;
border-radius:20px;
font-size:11px;
font-weight:bold;
color:white;
}

.badge-admin{background:#dc2626;}
.badge-user{background:#2563eb;}

.btn{
padding:6px 12px;
border:none;
border-radius:6px;
cursor:pointer;
font-size:12px;
text-decoration:none;
}

.btn-primary{
background:#16a34a;
color:white;
}

.btn-edit{
background:#2563eb;
color:white;
}

.btn-primary:hover{background:#15803d;}
.btn-edit:hover{background:#1d4ed8;}

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

<h2>User Management</h2>

<div class="card-modern">

<form method="GET" class="filter-bar">
<input type="hidden" name="module" value="users">
<input type="hidden" name="action" value="index">

<input type="text" name="search"
placeholder="Cari username / role..."
value="<?= $search ?>">

<select name="limit">
<?php foreach($allowedLimit as $l){ ?>
<option value="<?= $l ?>" <?= $limit==$l?'selected':'' ?>>
<?= $l ?> rows
</option>
<?php } ?>
</select>

<button type="submit">Filter</button>

<a href="index.php?module=users&action=create" class="btn btn-primary">
+ Tambah User
</a>

</form>

<table class="table-modern">
<tr>
<th>No</th>
<th>Username</th>
<th>Role</th>
<th>Aksi</th>
</tr>

<?php
$no=$offset+1;
while($row=mysqli_fetch_assoc($query)){

$roleBadge = strtolower($row['role'])=='admin'
? "<span class='badge badge-admin'>ADMIN</span>"
: "<span class='badge badge-user'>USER</span>";
?>
<tr>
<td><?= $no++; ?></td>
<td><?= htmlspecialchars($row['username']); ?></td>
<td><?= $roleBadge; ?></td>
<td>
<a class="btn btn-edit"
href="index.php?module=users&action=edit&id=<?= $row['id']; ?>">
Edit
</a>
</td>
</tr>
<?php } ?>
</table>

<div class="pagination">
<?php for($i=1;$i<=$totalPages;$i++){ ?>
<a class="<?= $i==$page?'active':'' ?>"
href="index.php?module=users&action=index&page=<?= $i ?>&search=<?= $search ?>&limit=<?= $limit ?>">
<?= $i ?>
</a>
<?php } ?>
</div>

</div>

</div></body></html>