<?php
checkLogin();
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$id = $_GET['id'];

/* ========================
   HEADER DO + CUSTOMER + WAREHOUSE
======================== */
$data = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT 
    d.*, 
    c.customer_name,
    w.name AS warehouse_name
FROM delivery_orders d
LEFT JOIN customers c ON d.customer_id = c.id
LEFT JOIN warehouses w ON d.warehouse_id = w.id
WHERE d.id='$id'
"));

if(!$data){
    echo "Data tidak ditemukan";
    exit;
}

/* ========================
   DETAIL ITEM
======================== */
$details = mysqli_query($conn,"
SELECT 
    dd.*, 
    i.item_code, 
    i.item_name
FROM delivery_order_details dd
JOIN items i ON dd.item_id = i.id
WHERE dd.do_id='$id'
");

$totalQty = 0;
$totalCbm = 0;
?>

<style>
.card{
background:white;
padding:20px;
border-radius:10px;
box-shadow:0 5px 15px rgba(0,0,0,0.08);
margin-bottom:20px;
}

table{
width:100%;
border-collapse:collapse;
background:white;
}

table th, table td{
border:1px solid #e5e7eb;
padding:8px;
font-size:13px;
}

table th{
background:#f3f4f6;
}
.badge{
padding:4px 8px;
border-radius:5px;
font-size:12px;
color:white;
}
.badge-posted{background:#16a34a;}
.badge-draft{background:#f59e0b;}
</style>

<h2>Detail Delivery Order</h2>

<div class="card">

<p><strong>No DO:</strong> <?= $data['do_number']; ?></p>
<p><strong>Gudang:</strong> <?= $data['warehouse_name'] ?? '-'; ?></p>
<p><strong>Customer:</strong> <?= $data['customer_name'] ?? '-'; ?></p>
<p><strong>Tanggal:</strong> <?= date('d-m-Y', strtotime($data['created_at'])); ?></p>
<p><strong>Status:</strong> 
    <?php if($data['status']=='POSTED'){ ?>
        <span class="badge badge-posted">POSTED</span>
    <?php } else { ?>
        <span class="badge badge-draft"><?= $data['status']; ?></span>
    <?php } ?>
</p>
<a href="index.php?module=delivery_orders&action=export_pdf&id=<?= $data['id']; ?>" 
class="btn btn-success" target="_blank">
Export PDF
</a>

</div>

<table>
<tr>
<th>No</th>
<th>Item Code</th>
<th>Item Name</th>
<th>Qty</th>
<th>CBM</th>
</tr>

<?php
$no=1;
while($row=mysqli_fetch_assoc($details)){
$totalQty += $row['qty'];
$totalCbm += $row['cbm'];
?>
<tr>
<td><?= $no++; ?></td>
<td><?= $row['item_code']; ?></td>
<td><?= $row['item_name']; ?></td>
<td><?= $row['qty']; ?></td>
<td><?= $row['cbm']; ?></td>
</tr>
<?php } ?>

<tr>
<td colspan="3" align="right"><strong>TOTAL</strong></td>
<td><strong><?= $totalQty; ?></strong></td>
<td><strong><?= $totalCbm; ?></strong></td>
</tr>

</table>

<br>
<a class="btn btn-primary"
href="index.php?module=delivery_orders&action=index">
Kembali
</a>

</div></body></html>