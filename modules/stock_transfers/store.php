<?php
checkLogin();
global $conn;

/* =========================
   DATA POST
========================= */

$from = $_POST['from_warehouse_id'];
$to   = $_POST['to_warehouse_id'];
$date = $_POST['transfer_date'];

if($from == $to){
    die("Gudang asal dan tujuan tidak boleh sama");
}

/* =========================
   GENERATE TRANSFER NUMBER
========================= */

$today = date('Ymd');

$q = mysqli_query($conn,"
SELECT MAX(transfer_number) as last_number
FROM stock_transfers
WHERE transfer_number LIKE 'ST-$today-%'
");

$data = mysqli_fetch_assoc($q);

if($data['last_number']){
    $last = explode('-', $data['last_number']);
    $urut = (int)$last[2] + 1;
}else{
    $urut = 1;
}

$urut = str_pad($urut,4,'0',STR_PAD_LEFT);

$transfer_number = "ST-$today-$urut";

/* =========================
   TRANSACTION START
========================= */

mysqli_begin_transaction($conn);

try{

    /* simpan header */
    mysqli_query($conn,"
    INSERT INTO stock_transfers
    (transfer_number,from_warehouse_id,to_warehouse_id,transfer_date)
    VALUES
    ('$transfer_number','$from','$to','$date')
    ");

    $transfer_id = mysqli_insert_id($conn);

    /* =========================
       LOOP ITEM
    ========================= */

    foreach($_POST['item_id'] as $i => $item){

        $qty = (int)$_POST['qty'][$i];

        if($qty <= 0) continue;

        /* cek stok gudang asal */
        $s = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT qty_available,total_cbm
        FROM stocks
        WHERE warehouse_id='$from'
        AND item_id='$item'
        "));

        if(!$s || $s['qty_available'] < $qty){
            throw new Exception("Stok tidak cukup untuk item ID $item");
        }

        /* hitung cbm */
        $cbm = ($s['total_cbm'] / $s['qty_available']) * $qty;

        /* simpan detail */
        mysqli_query($conn,"
        INSERT INTO stock_transfer_details
        (transfer_id,item_id,qty,cbm)
        VALUES
        ('$transfer_id','$item','$qty','$cbm')
        ");

        /* update stok gudang asal */
        mysqli_query($conn,"
        UPDATE stocks
        SET qty_available = qty_available - $qty,
            total_cbm = total_cbm - $cbm
        WHERE warehouse_id='$from'
        AND item_id='$item'
        ");

        /* update stok gudang tujuan */
        mysqli_query($conn,"
        INSERT INTO stocks
        (warehouse_id,item_id,qty_available,total_cbm)
        VALUES
        ('$to','$item','$qty','$cbm')
        ON DUPLICATE KEY UPDATE
        qty_available = qty_available + $qty,
        total_cbm = total_cbm + $cbm
        ");

        /* =========================
           STOCK CARD KELUAR
        ========================= */

        $balance_from = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT qty_available
        FROM stocks
        WHERE warehouse_id='$from'
        AND item_id='$item'
        "))['qty_available'];

        mysqli_query($conn,"
        INSERT INTO stock_cards
        (warehouse_id,item_id,transaction_type,reference_number,qty_in,qty_out,balance_after)
        VALUES
        ('$from','$item','TRANSFER','$transfer_number',0,'$qty','$balance_from')
        ");

        /* =========================
           STOCK CARD MASUK
        ========================= */

        $balance_to = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT qty_available
        FROM stocks
        WHERE warehouse_id='$to'
        AND item_id='$item'
        "))['qty_available'];

        mysqli_query($conn,"
        INSERT INTO stock_cards
        (warehouse_id,item_id,transaction_type,reference_number,qty_in,qty_out,balance_after)
        VALUES
        ('$to','$item','TRANSFER','$transfer_number','$qty',0,'$balance_to')
        ");
    }

    mysqli_commit($conn);

} catch(Exception $e){

    mysqli_rollback($conn);
    die("Error: ".$e->getMessage());

}

/* redirect */
header("Location:index.php?module=stock_transfers&action=index");
exit;