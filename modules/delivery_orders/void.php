<?php
checkLogin();
checkRole(['ADMIN','SUPERVISOR']);
global $conn;

$id = $_GET['id'];

mysqli_begin_transaction($conn);

try {

    $do = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT * FROM delivery_orders WHERE id='$id'
    "));

    if(!$do){
        throw new Exception("DO tidak ditemukan.");
    }

    if($do['status'] == 'VOID'){
        throw new Exception("DO sudah di-VOID.");
    }

    // Ambil detail
    $details = mysqli_query($conn,"
        SELECT * FROM delivery_order_details WHERE do_id='$id'
    ");

    while($d=mysqli_fetch_assoc($details)){

        // Tambah kembali stok
        mysqli_query($conn,"
            UPDATE stocks
            SET qty_available = qty_available + ".$d['qty']."
            WHERE warehouse_id='".$do['warehouse_id']."'
            AND item_id='".$d['item_id']."'
        ");

        // Ambil balance terbaru
        $balance = mysqli_fetch_assoc(mysqli_query($conn,"
            SELECT qty_available FROM stocks
            WHERE warehouse_id='".$do['warehouse_id']."'
            AND item_id='".$d['item_id']."'
        "))['qty_available'];

        // Insert stock card reverse
        mysqli_query($conn,"
            INSERT INTO stock_cards
            (warehouse_id,item_id,transaction_type,reference_number,qty_in,qty_out,balance_after)
            VALUES
            ('".$do['warehouse_id']."','".$d['item_id']."',
            'VOID_DO','".$do['do_number']."',
            '".$d['qty']."',0,'$balance')
        ");
    }

    // Update status
    mysqli_query($conn,"
        UPDATE delivery_orders
        SET status='VOID',
            void_by='".$_SESSION['user_id']."',
            void_at=NOW()
        WHERE id='$id'
    ");

    logActivity('Delivery Order','VOID','Void DO '.$do['do_number']);

    mysqli_commit($conn);

    header("Location:index.php?module=delivery_orders&action=index");
    exit;

} catch(Exception $e){

    mysqli_rollback($conn);
    echo "Gagal: ".$e->getMessage();
}