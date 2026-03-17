<?php
checkLogin();
checkRole(['ADMIN','WAREHOUSE']);
global $conn;

$id = $_GET['id'];

checkPeriodLock();

mysqli_begin_transaction($conn);

try {

    $doQuery = mysqli_query($conn,"SELECT * FROM delivery_orders WHERE id='$id'");
    $do = mysqli_fetch_assoc($doQuery);

    if(!$do) throw new Exception("DO tidak ditemukan.");
    if($do['status'] == 'CANCELLED') throw new Exception("DO sudah dicancel.");

    $warehouse_id = $do['warehouse_id'];
    $do_number    = $do['do_number'];

    $detailQuery = mysqli_query($conn,"
        SELECT * FROM delivery_order_details WHERE do_id='$id'
    ");

    while($detail = mysqli_fetch_assoc($detailQuery)){

        $item_id = $detail['item_id'];
        $qty     = $detail['qty'];
        $cbm     = $detail['cbm'];

        mysqli_query($conn,"
            UPDATE stocks
            SET qty_available = qty_available + $qty,
                total_cbm = total_cbm + $cbm
            WHERE warehouse_id='$warehouse_id'
            AND item_id='$item_id'
        ");

        $balanceQuery = mysqli_query($conn,"
            SELECT qty_available FROM stocks
            WHERE warehouse_id='$warehouse_id'
            AND item_id='$item_id'
        ");

        $balance = mysqli_fetch_assoc($balanceQuery)['qty_available'];

        mysqli_query($conn,"
            INSERT INTO stock_cards
            (warehouse_id,item_id,transaction_type,reference_number,qty_in,balance_after)
            VALUES
            ('$warehouse_id','$item_id','REVERSAL','$do_number','$qty','$balance')
        ");
    }

    mysqli_query($conn,"
        UPDATE delivery_orders
        SET status='CANCELLED'
        WHERE id='$id'
    ");
	
	logActivity(
    $_SESSION['user_id'],
    'Delivery Order',
    'CANCEL',
    'Cancel DO No: '.$do_number
);

    mysqli_commit($conn);

    header("Location: index.php?module=delivery_orders&action=index");
    exit;

} catch (Exception $e) {

    mysqli_rollback($conn);
    echo "Gagal cancel: ".$e->getMessage();
}