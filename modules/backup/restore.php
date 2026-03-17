<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

$backupDir = BASE_PATH."/storage/backups/";
$file = $_GET['file'] ?? '';

if(!$file){
    die("File tidak ditemukan");
}

$filepath = $backupDir.$file;

if(!file_exists($filepath)){
    die("File backup tidak ada");
}

if(pathinfo($filepath, PATHINFO_EXTENSION) != 'sql'){
    die("Format file tidak valid");
}

$mysqlPath = "C:\\laragon\\bin\\mysql\\mysql-8.0.34-winx64\\bin\\mysql.exe";

$command = "\"$mysqlPath\" -u root wms_kbr < \"$filepath\"";

system($command);

echo "<script>
alert('Restore berhasil dilakukan!');
window.location='index.php?module=backup&action=index';
</script>";

exit;