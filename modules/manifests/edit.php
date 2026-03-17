<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$id = $_GET['id'];

$query = mysqli_query($conn,"
SELECT * FROM manifests 
WHERE id='$id' AND is_deleted=0
");

$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Manifest tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $number  = trim($_POST['manifest_number']);
    $arrival = $_POST['arrival_date'];
    $status  = $_POST['status'];

    if ($number == "" || $arrival == "") {
        die("Semua field wajib diisi.");
    }

    $check = mysqli_query($conn,"
        SELECT id FROM manifests 
        WHERE manifest_number='$number'
        AND id != '$id'
        AND is_deleted=0
    ");

    if (mysqli_num_rows($check) > 0) {
        die("Nomor manifest sudah digunakan.");
    }

    mysqli_query($conn,"
        UPDATE manifests SET
        manifest_number='$number',
        arrival_date='$arrival',
        status='$status'
        WHERE id='$id'
    ");

    logActivity('Manifest','UPDATE','Update manifest '.$number);

    header("Location: index.php?module=manifests&action=index");
    exit;
}
?>

<h2>Edit Manifest</h2>

<div class="card">

<form method="POST">

<label>Nomor Manifest</label><br>
<input type="text" name="manifest_number"
value="<?= $data['manifest_number']; ?>" required>

<br><br>

<label>Tanggal Kedatangan</label><br>
<input type="date" name="arrival_date"
value="<?= $data['arrival_date']; ?>" required>

<br><br>

<label>Status</label><br>
<select name="status">
    <option value="OPEN" <?= $data['status']=='OPEN'?'selected':''; ?>>OPEN</option>
    <option value="CLOSED" <?= $data['status']=='CLOSED'?'selected':''; ?>>CLOSED</option>
</select>

<br><br>

<button class="btn btn-primary">Update</button>

<a href="index.php?module=manifests&action=index"
class="btn btn-warning">Kembali</a>

</form>

</div>

</div></body></html>