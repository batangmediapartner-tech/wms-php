<?php
checkLogin();
global $conn;

$id = $_GET['id'];

$do = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT do.*, w.name as warehouse_name
    FROM delivery_orders do
    JOIN warehouses w ON do.warehouse_id = w.id
    WHERE do.id='$id'
"));

if(!$do){
    die("DO tidak ditemukan.");
}

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=DO_".$do['do_number'].".xls");
header("Pragma: no-cache");
header("Expires: 0");

$details = mysqli_query($conn,"
    SELECT d.*, i.item_code, i.item_name
    FROM delivery_order_details d
    JOIN items i ON d.item_id=i.id
    WHERE d.do_id='$id'
");

$total_qty = 0;
$total_cbm = 0;
?>

<table border="1">
<tr>
    <th colspan="6" style="font-size:16px;">
        DELIVERY ORDER
    </th>
</tr>

<tr>
    <td><b>No DO</b></td>
    <td><?= $do['do_number']; ?></td>
    <td><b>Tanggal</b></td>
    <td><?= date('d-m-Y', strtotime($do['created_at'])); ?></td>
</tr>

<tr>
    <td><b>Gudang</b></td>
    <td><?= $do['warehouse_name']; ?></td>
    <td><b>Customer</b></td>
    <td><?= $do['customer_name']; ?></td>
</tr>

<tr><td colspan="6"></td></tr>

<tr style="background:#dddddd;">
    <th>No</th>
    <th>Item Code</th>
    <th>Item Name</th>
    <th>Qty</th>
    <th>CBM</th>
</tr>

<?php 
$no=1;
while($row=mysqli_fetch_assoc($details)){
    $total_qty += $row['qty'];
    $total_cbm += $row['cbm'];
?>
<tr>
    <td><?= $no++; ?></td>
    <td><?= $row['item_code']; ?></td>
    <td><?= $row['item_name']; ?></td>
    <td><?= $row['qty']; ?></td>
    <td><?= number_format($row['cbm'],2); ?></td>
</tr>
<?php } ?>

<tr>
    <th colspan="3">TOTAL</th>
    <th><?= $total_qty; ?></th>
    <th><?= number_format($total_cbm,2); ?></th>
</tr>

<tr><td colspan="6"></td></tr>

<tr>
    <td colspan="6">
        Dicetak oleh: <?= $_SESSION['name']; ?> | <?= date('d-m-Y H:i'); ?>
    </td>
</tr>

</table>