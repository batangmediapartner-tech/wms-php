<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $number  = trim($_POST['manifest_number']);
    $arrival = $_POST['arrival_date'];

    if ($number == "" || $arrival == "") {
        die("Semua field wajib diisi.");
    }

    $check = mysqli_query($conn,"
        SELECT id FROM manifests 
        WHERE manifest_number='$number' 
        AND is_deleted=0
    ");

    if (mysqli_num_rows($check) > 0) {
        die("Nomor manifest sudah ada.");
    }

    mysqli_query($conn,"
        INSERT INTO manifests
        (manifest_number,arrival_date,status,is_deleted)
        VALUES
        ('$number','$arrival','OPEN',0)
    ");

    logActivity('Manifest','CREATE','Tambah manifest '.$number);

    header("Location: index.php?module=manifests&action=index");
    exit;
}
?>

<h2>Tambah Manifest</h2>

<div class="card">

<form method="POST">

<label>Nomor Manifest</label><br>
<input type="text" name="manifest_number" required>

<br><br>

<label>Tanggal Kedatangan</label><br>
<input type="date" name="arrival_date" required>

<br><br>

<button class="btn btn-primary">Simpan</button>

<a href="index.php?module=manifests&action=index"
class="btn btn-warning">Kembali</a>

</form>

</div>

</div></body></html>