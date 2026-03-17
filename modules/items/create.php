<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $code = trim($_POST['item_code']);
    $name = trim($_POST['item_name']);
    $cbm  = trim($_POST['cbm']);
    $minimum_stock = (int)$_POST['minimum_stock'];

    if ($code == "" || $name == "" || $cbm == "") {
        die("Semua field wajib diisi.");
    }

    $check = mysqli_query($conn, "
        SELECT id FROM items 
        WHERE item_code='$code' 
        AND is_deleted=0
    ");

    if (mysqli_num_rows($check) > 0) {
        die("Kode item sudah ada.");
    }

    mysqli_query($conn, "
        INSERT INTO items 
        (item_code,item_name,cbm,minimum_stock,is_active,is_deleted)
        VALUES 
        ('$code','$name','$cbm','$minimum_stock',1,0)
    ");

    logActivity('Item','CREATE','Tambah item '.$code);

    header("Location: index.php?module=items&action=index");
    exit;
}
?>

<h2>Tambah Item</h2>

<div class="card">

<form method="POST">

<label>Kode Item</label><br>
<input type="text" name="item_code" required>

<br><br>

<label>Nama Item</label><br>
<input type="text" name="item_name" required>

<br><br>

<label>CBM per Unit</label><br>
<input type="number" step="0.000001" name="cbm" required>

<br><br>

<label>Minimum Stock</label><br>
<input type="number" name="minimum_stock" value="0">

<br><br>

<button class="btn btn-primary">Simpan</button>

<a href="index.php?module=items&action=index"
class="btn btn-warning">Kembali</a>

</form>

</div>

</div></body></html>