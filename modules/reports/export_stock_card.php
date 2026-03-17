<?php
checkLogin();
checkRole(['ADMIN','WAREHOUSE']);
global $conn;

$warehouse_id = isset($_GET['warehouse_id']) ? $_GET['warehouse_id'] : '';
$item_code    = isset($_GET['item_code']) ? $_GET['item_code'] : '';
$customer     = isset($_GET['customer']) ? $_GET['customer'] : '';
$date_from    = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to      = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$where = "WHERE 1=1";

if($warehouse_id != ''){
    $where .= " AND sc.warehouse_id = '$warehouse_id'";
}

if($item_code != ''){
    $where .= " AND i.item_code LIKE '%$item_code%'";
}

if($customer != ''){
    $where .= " AND (
        do.customer_name LIKE '%$customer%' 
        OR m.manifest_number LIKE '%$customer%'
    )";
}

if($date_from != ''){
    $where .= " AND DATE(sc.created_at) >= '$date_from'";
}

if($date_to != ''){
    $where .= " AND DATE(sc.created_at) <= '$date_to'";
}

$query = mysqli_query($conn,"
    SELECT sc.*, 
           w.name as warehouse_name,
           i.item_code,
           do.customer_name,
           m.manifest_number
    FROM stock_cards sc
    JOIN warehouses w ON sc.warehouse_id = w.id
    JOIN items i ON sc.item_id = i.id
    LEFT JOIN delivery_orders do 
        ON sc.reference_number = do.do_number
    LEFT JOIN goods_receipts gr 
        ON sc.reference_number = gr.gr_number
    LEFT JOIN manifests m
        ON gr.manifest_id = m.id
    $where
    ORDER BY sc.created_at ASC
");

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Kartu_Stok.xls");

echo "No\tTanggal\tGudang\tKode Barang\tCustomer/Manifest\tTransaksi\tReferensi\tQty In\tQty Out\tSaldo Setelah\n";

$no=1;

while($row=mysqli_fetch_assoc($query)){

    $customer_display = '-';

    if($row['transaction_type'] == 'DO'){
        $customer_display = $row['customer_name'] ? $row['customer_name'] : '-';
    } else {
        $customer_display = $row['manifest_number'] ? $row['manifest_number'] : '-';
    }

    echo $no++ . "\t";
    echo $row['created_at'] . "\t";
    echo $row['warehouse_name'] . "\t";
    echo $row['item_code'] . "\t";
    echo $customer_display . "\t";
    echo $row['transaction_type'] . "\t";
    echo $row['reference_number'] . "\t";
    echo $row['qty_in'] . "\t";
    echo $row['qty_out'] . "\t";
    echo $row['balance_after'] . "\n";
}

exit;