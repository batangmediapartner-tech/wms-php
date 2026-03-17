<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

if(isset($_POST['save'])){

    $name     = mysqli_real_escape_string($conn,$_POST['name']);
    $capacity = (float)$_POST['capacity_cbm'];
    $address  = mysqli_real_escape_string($conn,$_POST['address']);

    $sql = "INSERT INTO warehouses
            (name,address,capacity_cbm,is_active,is_deleted)
            VALUES
            ('$name','$address','$capacity',1,0)";

    mysqli_query($conn,$sql);

    logActivity('Warehouse','CREATE','Buat gudang '.$name);

    header("Location:index.php?module=warehouses&action=index");
    exit;
}
?>

<h2>Tambah Gudang</h2>

<div class="card">
<form method="POST">

<label>Nama Gudang</label><br>
<input type="text" name="name" required>

<br><br>

<label>Kapasitas (CBM)</label><br>
<input type="number" step="0.01" name="capacity_cbm" required>

<br><br>

<label>Alamat</label><br>
<textarea name="address"></textarea>

<br><br>

<button type="submit" name="save" class="btn btn-primary">
Simpan
</button>

<a href="index.php?module=warehouses&action=index"
class="btn btn-warning">
Kembali
</a>

</form>
</div>

</div></body></html>