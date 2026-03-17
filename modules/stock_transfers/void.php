<?php
checkLogin();
global $conn;

$id = $_GET['id'];

mysqli_begin_transaction($conn);

try{

/* ambil header transfer */
$transfer = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT * FROM stock_transfers
WHERE id='$id'
"));

if(!$transfer){
throw new Exception("Transfer tidak ditemukan");
}

if($transfer['status']=='VOID'){
throw new Exception("Transfer sudah di VOID");
}

$from = $transfer['from_warehouse_id'];
$to   = $transfer['to_warehouse_id'];
$ref  = $transfer['transfer_number'];

/* ambil detail item */
$details = mysqli_query($conn,"
SELECT * FROM stock_transfer_details
WHERE transfer_id='$id'
");

while($d = mysqli_fetch_assoc($details)){

$item = $d['item_id'];
$qty  = $d['qty'];
$cbm  = $d['cbm'];

/* kembalikan stok ke gudang asal */

mysqli_query($conn,"
UPDATE stocks
SET qty_available = qty_available + $qty,
total_cbm = total_cbm + $cbm
WHERE warehouse_id='$from'
AND item_id='$item'
");

/* kurangi stok gudang tujuan */

mysqli_query($conn,"
UPDATE stocks
SET qty_available = qty_available - $qty,
total_cbm = total_cbm - $cbm
WHERE warehouse_id='$to'
AND item_id='$item'
");

/* balance asal */

$balance_from = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT qty_available FROM stocks
WHERE warehouse_id='$from'
AND item_id='$item'
"))['qty_available'];

/* stock card reversal asal */

mysqli_query($conn,"
INSERT INTO stock_cards
(warehouse_id,item_id,transaction_type,reference_number,qty_in,qty_out,balance_after)
VALUES
('$from','$item','TRANSFER VOID','$ref','$qty',0,'$balance_from')
");

/* balance tujuan */

$balance_to = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT qty_available FROM stocks
WHERE warehouse_id='$to'
AND item_id='$item'
"))['qty_available'];

/* stock card reversal tujuan */

mysqli_query($conn,"
INSERT INTO stock_cards
(warehouse_id,item_id,transaction_type,reference_number,qty_in,qty_out,balance_after)
VALUES
('$to','$item','TRANSFER VOID','$ref',0,'$qty','$balance_to')
");

}

/* update status */

mysqli_query($conn,"
UPDATE stock_transfers
SET status='VOID'
WHERE id='$id'
");

mysqli_commit($conn);

} catch(Exception $e){

mysqli_rollback($conn);
die($e->getMessage());

}

header("Location:index.php?module=stock_transfers&action=index");
exit;