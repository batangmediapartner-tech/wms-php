<?php
checkLogin();
checkRole(['ADMIN','WAREHOUSE']);
global $conn;

$warehouse_id = isset($_GET['warehouse_id']) ? $_GET['warehouse_id'] : '';
$item_code    = isset($_GET['item_code']) ? $_GET['item_code'] : '';

$where = "WHERE 1=1";

if($warehouse_id != ''){
    $where .= " AND s.warehouse_id = '$warehouse_id'";
}

if($item_code != ''){
    $where .= " AND i.item_code LIKE '%$item_code%'";
}

$query = mysqli_query($conn,"
    SELECT s.*, 
           w.name as warehouse_name,
           i.item_code,
           i.item_name
    FROM stocks s
    JOIN warehouses w ON s.warehouse_id = w.id
    JOIN items i ON s.item_id = i.id
    $where
    ORDER BY w.name, i.item_code
");

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Stok_Realtime.xls");

echo "No\tGudang\tKode Barang\tNama Barang\tQty Available\tTotal CBM\n";

$no=1;

while($row=mysqli_fetch_assoc($query)){

    echo $no++ . "\t";
    echo $row['warehouse_name'] . "\t";
    echo $row['item_code'] . "\t";
    echo $row['item_name'] . "\t";
    echo $row['qty_available'] . "\t";
    echo number_format($row['total_cbm'],4) . "\n";
}

exit;