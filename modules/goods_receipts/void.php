<?php
checkLogin();
checkRole(['ADMIN','SUPERVISOR']);
global $conn;

$id = $_GET['id'];

mysqli_begin_transaction($conn);

try {

    $gr = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT * FROM goods_receipts WHERE id='$id'
    "));

    if(!$gr){
        throw new Exception("GR tidak ditemukan.");
    }

    if($gr['status'] == 'VOID'){
        throw new Exception("GR sudah di-VOID.");
    }

    $details = mysqli_query($conn,"
        SELECT * FROM goods_receipt_details WHERE gr_id='$id'
    ");

    while($d=mysqli_fetch_assoc($details)){

        // Kurangi kembali stok
        mysqli_query($conn,"
            UPDATE stocks
            SET qty_available = qty_available - ".$d['qty']."
            WHERE warehouse_id='".$gr['warehouse_id']."'
            AND item_id='".$d['item_id']."'
        ");

        $balance = mysqli_fetch_assoc(mysqli_query($conn,"
            SELECT qty_available FROM stocks
            WHERE warehouse_id='".$gr['warehouse_id']."'
            AND item_id='".$d['item_id']."'
        "))['qty_available'];

        // Insert reverse stock card
        mysqli_query($conn,"
            INSERT INTO stock_cards
            (warehouse_id,item_id,transaction_type,reference_number,qty_in,qty_out,balance_after)
            VALUES
            ('".$gr['warehouse_id']."','".$d['item_id']."',
            'VOID_GR','".$gr['gr_number']."',
            0,'".$d['qty']."','$balance')
        ");
    }

    mysqli_query($conn,"
        UPDATE goods_receipts
        SET status='VOID',
            void_by='".$_SESSION['user_id']."',
            void_at=NOW()
        WHERE id='$id'
    ");

    logActivity('Goods Receipt','VOID','Void GR '.$gr['gr_number']);

    mysqli_commit($conn);

    header("Location:index.php?module=goods_receipts&action=index");
    exit;

} catch(Exception $e){

    mysqli_rollback($conn);
    echo "Gagal: ".$e->getMessage();
}