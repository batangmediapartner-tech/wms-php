<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$id = $_GET['id'];

$query = mysqli_query($conn,"
SELECT * FROM items 
WHERE id='$id' AND is_deleted=0
");

$item = mysqli_fetch_assoc($query);

if (!$item) {
    die("Item tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $code = trim($_POST['item_code']);
    $name = trim($_POST['item_name']);
    $cbm  = trim($_POST['cbm']);
    $minimum_stock = (int)$_POST['minimum_stock'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($code == "" || $name == "" || $cbm == "") {
        die("Semua field wajib diisi.");
    }

    $check = mysqli_query($conn,"
        SELECT id FROM items 
        WHERE item_code='$code'
        AND id != '$id'
        AND is_deleted=0
    ");

    if (mysqli_num_rows($check) > 0) {
        die("Kode item sudah digunakan item lain.");
    }

    mysqli_query($conn,"
        UPDATE items SET
        item_code='$code',
        item_name='$name',
        cbm='$cbm',
        minimum_stock='$minimum_stock',
        is_active='$is_active'
        WHERE id='$id'
    ");

    logActivity('Item','UPDATE','Update item '.$code);

    header("Location: index.php?module=items&action=index");
    exit;
}
?>

<h2>Edit Item</h2>

<div class="card">

<form method="POST">

<label>Kode Item</label><br>
<input type="text" name="item_code"
value="<?= $item['item_code']; ?>" required>

<br><br>

<label>Nama Item</label><br>
<input type="text" name="item_name"
value="<?= $item['item_name']; ?>" required>

<br><br>

<label>CBM per Unit</label><br>
<input type="number" step="0.000001"
name="cbm"
value="<?= $item['cbm']; ?>" required>

<br><br>

<label>Minimum Stock</label><br>
<input type="number"
name="minimum_stock"
value="<?= $item['minimum_stock']; ?>">

<br><br>

<label>
<input type="checkbox" name="is_active"
<?= $item['is_active']?'checked':''; ?>>
 Aktif
</label>

<br><br>

<button class="btn btn-primary">Update</button>

<a href="index.php?module=items&action=index"
class="btn btn-warning">Kembali</a>

</form>

</div>

</div></body></html>