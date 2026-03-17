<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$backupDir = BASE_PATH."/storage/backups/";

/* AUTO CREATE FOLDER */
if(!is_dir($backupDir)){
    mkdir($backupDir, 0777, true);
}

/* ===============================
   CREATE BACKUP MANUAL
================================ */
if(isset($_POST['create_backup'])){

    $filename = "wms_kbr_".date("Y-m-d_H-i-s").".sql";
    $filepath = $backupDir.$filename;

    $mysqldumpPath = "C:\\laragon\\bin\\mysql\\mysql-8.0.34-winx64\\bin\\mysqldump.exe";

    $command = "\"$mysqldumpPath\" -u root wms_kbr > \"$filepath\"";

    system($command);

    echo "<script>alert('Backup berhasil dibuat');</script>";
}

/* ===============================
   LIST FILE BACKUP
================================ */
$files = array_diff(scandir($backupDir), ['.','..']);
?>

<h2>Database Backup & Restore</h2>

<div class="card">

<form method="POST">
<button type="submit" name="create_backup" class="btn btn-success">
Buat Backup Sekarang
</button>
</form>

<br>

<table border="1" cellpadding="6" width="100%">
<tr>
<th>No</th>
<th>File</th>
<th>Ukuran</th>
<th>Aksi</th>
</tr>

<?php
$no=1;
foreach(array_reverse($files) as $file){

if(pathinfo($file, PATHINFO_EXTENSION) != 'sql') continue;

$size = round(filesize($backupDir.$file)/1024,2)." KB";
?>
<tr>
<td><?= $no++; ?></td>
<td><?= $file; ?></td>
<td><?= $size; ?></td>
<td>

<a href="storage/backups/<?= $file; ?>" 
class="btn btn-primary" download>
Download
</a>

<a href="index.php?module=backup&action=restore&file=<?= urlencode($file); ?>" 
class="btn btn-danger"
onclick="return confirm('PERINGATAN! Restore akan mengganti seluruh database. Lanjutkan?')">
Restore
</a>

</td>
</tr>
<?php } ?>
</table>

</div>

</div></body></html>