<?php

checkLogin();
global $conn;

require BASE_PATH."/vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = $_FILES['excel']['tmp_name'];

$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet()->toArray();

$data = [];

foreach($sheet as $key=>$row){

if($key==0) continue;

$item_code = trim($row[0]);
$qty       = (int)$row[1];

if($item_code=='' || $qty<=0) continue;

$item = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT id,item_code,item_name,cbm
FROM items
WHERE item_code='$item_code'
"));

if(!$item) continue;

$data[]=[
'id'=>$item['id'],
'code'=>$item['item_code'],
'name'=>$item['item_name'],
'qty'=>$qty,
'cbm'=>$item['cbm']
];

}

$_SESSION['gr_import']=$data;

header("Location:index.php?module=goods_receipts&action=create");
exit;