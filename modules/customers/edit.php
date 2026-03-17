<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$id = $_GET['id'] ?? 0;

$data = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT * FROM customers WHERE id='$id'
"));

if(!$data){
    echo "<div class='card'>Customer tidak ditemukan.</div>";
    echo "</div></body></html>";
    exit;
}

/* =========================
   UPDATE CUSTOMER
========================= */
if(isset($_POST['update'])){

    $code    = mysqli_real_escape_string($conn,$_POST['customer_code']);
    $name    = mysqli_real_escape_string($conn,$_POST['customer_name']);
    $address = mysqli_real_escape_string($conn,$_POST['address']);
    $phone   = mysqli_real_escape_string($conn,$_POST['phone']);

    mysqli_query($conn,"
        UPDATE customers SET
        customer_code='$code',
        customer_name='$name',
        address='$address',
        phone='$phone'
        WHERE id='$id'
    ");

    logActivity('Customer','UPDATE','Update customer '.$code);

    header("Location:index.php?module=customers&action=index");
    exit;
}

/* =========================
   NONAKTIFKAN CUSTOMER
========================= */
if(isset($_POST['nonaktif'])){

    mysqli_query($conn,"
        UPDATE customers SET is_active=0 WHERE id='$id'
    ");

    logActivity('Customer','NONAKTIF','Nonaktif customer '.$data['customer_code']);

    header("Location:index.php?module=customers&action=index");
    exit;
}
?>

<h2>Edit Customer</h2>

<div class="card">

<form method="POST">

<label>Kode Customer</label><br>
<input type="text" name="customer_code"
value="<?= $data['customer_code']; ?>" required>

<br><br>

<label>Nama Customer</label><br>
<input type="text" name="customer_name"
value="<?= $data['customer_name']; ?>" required>

<br><br>

<label>Alamat</label><br>
<textarea name="address"><?= $data['address']; ?></textarea>

<br><br>

<label>Telepon</label><br>
<input type="text" name="phone"
value="<?= $data['phone']; ?>">

<br><br>

<button type="submit" name="update"
class="btn btn-primary">
Update
</button>

<button type="submit" name="nonaktif"
class="btn btn-danger"
onclick="return confirm('Yakin nonaktifkan customer ini?')">
Nonaktifkan
</button>

<a href="index.php?module=customers&action=index"
class="btn btn-warning">
Kembali
</a>

</form>

</div>

</div>
</body>
</html>