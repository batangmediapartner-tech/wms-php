<?php
checkLogin();
global $conn;

$warehouse_id = $_GET['warehouse_id'];

$data = [];

$query = mysqli_query($conn,"
    SELECT s.item_id, s.qty_available, i.item_code
    FROM stocks s
    JOIN items i ON s.item_id=i.id
    WHERE s.warehouse_id='$warehouse_id'
    AND s.qty_available > 0
");

while($row=mysqli_fetch_assoc($query)){
    $data[$row['item_id']] = [
        'qty'=>$row['qty_available'],
        'item_code'=>$row['item_code']
    ];
}

echo json_encode($data);