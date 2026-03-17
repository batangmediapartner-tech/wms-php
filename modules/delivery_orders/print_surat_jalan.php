<?php
checkLogin();
global $conn;

$id = $_GET['id'] ?? 0;

$do = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT 
do.*,
c.customer_name,
c.address
FROM delivery_orders do
LEFT JOIN customers c ON c.id = do.customer_id
WHERE do.id='$id'
"));

$items = mysqli_query($conn,"
SELECT 
d.*,
i.item_code
FROM delivery_order_details d
LEFT JOIN items i ON i.id = d.item_id
WHERE d.do_id='$id'
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<title>Print Surat Jalan</title>

<style>

body{
width:21cm;
height:14cm;
font-family: Courier New, monospace;
font-size:12px;
margin:0;
padding:10px;
}

.header{
text-align:center;
line-height:1.3;
}

.title{
text-align:center;
font-weight:bold;
font-size:16px;
margin-top:5px;
}

hr{
border:1px solid black;
}

.info{
width:100%;
margin-top:5px;
}

.info td{
padding:2px;
vertical-align:top;
}

.table_items{
width:100%;
border-collapse:collapse;
margin-top:8px;
}

.table_items th,
.table_items td{
border:1px solid black;
padding:4px;
text-align:center;
}

.ttd{
width:100%;
margin-top:20px;
}

.ttd td{
width:50%;
}

.cutline{
margin-top:10px;
border-top:1px dashed black;
text-align:center;
font-size:10px;
}

@media print{

button{
display:none;
}

}

</style>

</head>

<body>

<div class="header">

<b>PT KIBAR RAYA LOGISTIK</b><br>
Jalan Segaran 3/5 Ngaliyan Semarang<br>
Telp 042-7649032

</div>

<hr>

<div class="title">
SURAT JALAN
</div>

<table class="info">

<tr>
<td width="140">No Surat Jalan</td>
<td>: <?= $do['do_number']; ?></td>
</tr>

<tr>
<td>Tanggal</td>
<td>: <?= date('d-m-Y',strtotime($do['created_at'] ?? date('Y-m-d'))); ?></td>
</tr>

<tr>
<td>Customer</td>
<td>: <?= $do['customer_name']; ?></td>
</tr>

<tr>
<td>Alamat</td>
<td>: <?= $do['address']; ?></td>
</tr>

<tr>
<td>Driver</td>
<td>: <?= $do['driver_name']; ?></td>
</tr>

<tr>
<td>No Polisi</td>
<td>: <?= $do['vehicle_no']; ?></td>
</tr>

<tr>
<td>Penerima</td>
<td>: <?= $do['receiver_name']; ?></td>
</tr>

</table>


<table class="table_items">

<tr>
<th width="40">No</th>
<th>Model</th>
<th width="70">Qty</th>
<th width="120">No DO</th>
<th width="120">No PO</th>
</tr>

<?php
$no=1;
while($i=mysqli_fetch_assoc($items)){
?>

<tr>
<td><?= $no++; ?></td>
<td><?= $i['item_code']; ?></td>
<td><?= $i['qty']; ?></td>
<td><?= $i['no_do']; ?></td>
<td><?= $i['no_po']; ?></td>
</tr>

<?php } ?>

</table>


<table class="ttd">

<tr>

<td align="left">

Pengirim

<br><br><br>

(............................)

</td>


<td align="right">

Penerima

<br><br><br>

(............................)

</td>

</tr>

</table>


<div class="cutline">
-------------------- POTONG DISINI --------------------
</div>

<br>

<button onclick="window.print()">
Print
</button>

</body>
</html>