<?php

checkLogin();
checkRole(['ADMIN','WAREHOUSE']);

require BASE_PATH.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if(isset($_POST['import'])){

$file = $_FILES['file']['tmp_name'];

$spreadsheet = IOFactory::load($file);

$data = $spreadsheet->getActiveSheet()->toArray();

$_SESSION['sj_import'] = [];

foreach($data as $i=>$row){

if($i==0) continue;

$model = trim($row[0]);
$qty   = intval($row[1]);
$do    = trim($row[2]);
$po    = trim($row[3]);

if(!$model || !$qty) continue;

$_SESSION['sj_import'][]=[
'model'=>$model,
'qty'=>$qty,
'no_do'=>$do,
'no_po'=>$po
];

}

header("Location: index.php?module=surat_jalan&action=create");
exit;

}

?>

<h2>Import Excel Surat Jalan</h2>

<form method="POST" enctype="multipart/form-data">

<input type="file" name="file" required>

<button name="import" class="btn btn-primary">
Import
</button>

</form>