<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

if(isset($_POST['save'])){

    $code = $_POST['customer_code'];
    $name = $_POST['customer_name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    mysqli_query($conn,"
        INSERT INTO customers
        (customer_code,customer_name,address,phone)
        VALUES
        ('$code','$name','$address','$phone')
    ");

    header("Location:index.php?module=customers&action=index");
    exit;
}
?>

<h2>Tambah Customer</h2>

<div class="card">
<form method="POST">

Kode:<br>
<input type="text" name="customer_code" required><br><br>

Nama:<br>
<input type="text" name="customer_name" required><br><br>

Alamat:<br>
<textarea name="address"></textarea><br><br>

Telepon:<br>
<input type="text" name="phone"><br><br>

<button type="submit" name="save" class="btn btn-primary">
Simpan
</button>

</form>
</div>

</div></body></html>