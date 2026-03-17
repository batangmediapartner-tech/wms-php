<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

/* =============================
   CREATE PERIODE
============================= */
if(isset($_POST['create_period'])){

    $month = (int)$_POST['month'];
    $year  = (int)$_POST['year'];

    $check = mysqli_query($conn,"
        SELECT id FROM period_locks
        WHERE month='$month' AND year='$year'
    ");

    if(mysqli_num_rows($check)==0){
        mysqli_query($conn,"
            INSERT INTO period_locks (month,year,is_locked)
            VALUES ('$month','$year',0)
        ");
    }

    header("Location: index.php?module=period_lock&action=index");
    exit;
}

/* =============================
   DATA LIST
============================= */
$query = mysqli_query($conn,"
SELECT * FROM period_locks
ORDER BY year DESC, month DESC
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
padding:5px 12px;
border-radius:20px;
font-size:11px;
font-weight:bold;
color:white;
}

.badge-locked{background:#dc2626;}
.badge-open{background:#16a34a;}

.btn{
padding:6px 12px;
border:none;
border-radius:6px;
cursor:pointer;
font-size:12px;
text-decoration:none;
}

.btn-lock{
background:#dc2626;
color:white;
}

.btn-unlock{
background:#2563eb;
color:white;
}

.btn-primary{
background:#16a34a;
color:white;
}

.btn-lock:hover{background:#b91c1c;}
.btn-unlock:hover{background:#1d4ed8;}
.btn-primary:hover{background:#15803d;}

.form-row{
display:flex;
gap:10px;
flex-wrap:wrap;
align-items:center;
margin-bottom:20px;
}

.form-row select{
padding:6px 10px;
border-radius:6px;
border:1px solid #ddd;
}
</style>

<h2>Period Lock Management</h2>

<div class="card-modern">

<!-- CREATE FORM -->
<form method="POST" class="form-row">
<select name="month" required>
<option value="">-- Pilih Bulan --</option>
<?php
for($m=1;$m<=12;$m++){
$monthName = date("F", mktime(0,0,0,$m,10));
echo "<option value='$m'>$monthName</option>";
}
?>
</select>

<select name="year" required>
<option value="">-- Pilih Tahun --</option>
<?php
$currentYear = date("Y");
for($y=$currentYear-2;$y<=$currentYear+2;$y++){
echo "<option value='$y'>$y</option>";
}
?>
</select>

<button type="submit" name="create_period" class="btn btn-primary">
+ Tambah Periode
</button>
</form>

<!-- TABLE -->
<table class="table-modern">
<tr>
<th>No</th>
<th>Periode</th>
<th>Status</th>
<th>Locked By</th>
<th>Locked At</th>
<th>Action</th>
</tr>

<?php
$no=1;
while($row=mysqli_fetch_assoc($query)){

$monthName = date("F", mktime(0,0,0,$row['month'],10));
$statusBadge = $row['is_locked']
    ? "<span class='badge badge-locked'>LOCKED</span>"
    : "<span class='badge badge-open'>OPEN</span>";
?>
<tr>
<td><?= $no++; ?></td>
<td><?= $monthName." ".$row['year']; ?></td>
<td><?= $statusBadge; ?></td>
<td><?= $row['locked_by'] ?? '-'; ?></td>
<td><?= $row['locked_at'] ?? '-'; ?></td>
<td>
<?php if($row['is_locked']){ ?>
<a class="btn btn-unlock"
href="index.php?module=period_lock&action=unlock&id=<?= $row['id']; ?>"
onclick="return confirm('Yakin membuka periode ini?')">
Unlock
</a>
<?php } else { ?>
<a class="btn btn-lock"
href="index.php?module=period_lock&action=lock&id=<?= $row['id']; ?>"
onclick="return confirm('Yakin mengunci periode ini?')">
Lock
</a>
<?php } ?>
</td>
</tr>
<?php } ?>
</table>

</div>

</div></body></html>