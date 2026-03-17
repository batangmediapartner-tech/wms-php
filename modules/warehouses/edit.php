<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$id = $_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT * FROM warehouses WHERE id='$id'
"));

if(!$data){
    die("Gudang tidak ditemukan.");
}

if(isset($_POST['update'])){

    $name     = mysqli_real_escape_string($conn,$_POST['name']);
    $capacity = (float)$_POST['capacity_cbm'];
    $address  = mysqli_real_escape_string($conn,$_POST['address']);
    $status   = (int)$_POST['is_active'];

    mysqli_query($conn,"
        UPDATE warehouses SET
        name='$name',
        address='$address',
        capacity_cbm='$capacity',
        is_active='$status'
        WHERE id='$id'
    ");

    logActivity('Warehouse','UPDATE','Update gudang '.$name);

    header("Location:index.php?module=warehouses&action=index");
    exit;
}

if(isset($_POST['delete'])){
    mysqli_query($conn,"
        UPDATE warehouses SET is_deleted=1 WHERE id='$id'
    ");
    header("Location:index.php?module=warehouses&action=index");
    exit;
}
?>

<h2>Edit Gudang</h2>

<div class="card">
<form method="POST">

<label>Nama Gudang</label><br>
<input type="text" name="name"
value="<?= $data['name']; ?>" required>

<br><br>

<label>Kapasitas (CBM)</label><br>
<input type="number" step="0.01"
name="capacity_cbm"
value="<?= $data['capacity_cbm']; ?>" required>

<br><br>

<label>Alamat</label><br>
<textarea name="address"><?= $data['address']; ?></textarea>

<br><br>

<label>Status</label><br>
<select name="is_active">
<option value="1" <?= $data['is_active']?'selected':'' ?>>Active</option>
<option value="0" <?= !$data['is_active']?'selected':'' ?>>Non Active</option>
</select>

<br><br>

<button name="update" class="btn btn-primary">Update</button>

<button name="delete" class="btn btn-danger"
onclick="return confirm('Yakin hapus gudang?')">
Delete
</button>

</form>
</div>

</div></body></html>