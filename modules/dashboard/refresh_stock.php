<?php
checkLogin();
checkRole(['ADMIN']);

global $conn;

mysqli_begin_transaction($conn);

try{

    /* ===============================
       RECALCULATE QTY FROM STOCK CARD
    =============================== */

    mysqli_query($conn,"
        UPDATE stocks s
        JOIN (
            SELECT 
                warehouse_id,
                item_id,
                SUM(qty_in - qty_out) AS real_qty
            FROM stock_cards
            GROUP BY warehouse_id,item_id
        ) x
        ON s.warehouse_id = x.warehouse_id
        AND s.item_id = x.item_id
        SET s.qty_available = x.real_qty
    ");

    /* ===============================
       RECALCULATE CBM
    =============================== */

    mysqli_query($conn,"
        UPDATE stocks s
        JOIN items i ON s.item_id=i.id
        SET s.total_cbm = s.qty_available * i.cbm
    ");

    mysqli_commit($conn);

    logActivity('Stock','REFRESH','Refresh stock system dari dashboard');

    header("Location:index.php?module=dashboard&action=index");
    exit;

}catch(Exception $e){

    mysqli_rollback($conn);
    echo "Gagal: ".$e->getMessage();
}