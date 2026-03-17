<?php

checkLogin();
global $conn;

require BASE_PATH."/vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\IOFactory;

if(!isset($_FILES['excel'])){
    header("Location:index.php?module=delivery_orders&action=create");
    exit;
}

$file = $_FILES['excel']['tmp_name'];

$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet()->toArray();

$data = [];

foreach($sheet as $key => $row){

    // skip header
    if($key == 0) continue;

    // hindari error trim(null)
    $item_code = trim($row[0] ?? '');
    $qty       = (int)($row[1] ?? 0);

    // skip jika kosong
    if($item_code == '' || $qty <= 0) continue;

    $item_code = mysqli_real_escape_string($conn,$item_code);

    $item = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT id,item_code,item_name,cbm
        FROM items
        WHERE item_code='$item_code'
        LIMIT 1
    "));

    // jika item tidak ada di master
    if(!$item) continue;

    $data[] = [
        'id'   => $item['id'],
        'code' => $item['item_code'],
        'name' => $item['item_name'],
        'qty'  => $qty,
        'cbm'  => $item['cbm']
    ];

}

$_SESSION['do_import'] = $data;

header("Location:index.php?module=delivery_orders&action=create");
exit;