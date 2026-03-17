<?php
require_once BASE_PATH."/library/tcpdf/tcpdf.php";
global $conn;

$id = $_GET['id'];

/* ================= HEADER DATA ================= */
$data = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT d.*, 
       c.customer_name,
       w.name AS warehouse_name
FROM delivery_orders d
LEFT JOIN customers c ON d.customer_id=c.id
LEFT JOIN warehouses w ON d.warehouse_id=w.id
WHERE d.id='$id'
"));

$details = mysqli_query($conn,"
SELECT dd.*, i.item_code, i.item_name
FROM delivery_order_details dd
JOIN items i ON dd.item_id=i.id
WHERE dd.do_id='$id'
");

/* ================= PDF SETUP ================= */
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica','',10);

/* ================= HEADER ================= */
$html = "
<h2>DELIVERY ORDER</h2>
<hr>
<p><strong>No DO:</strong> {$data['do_number']}</p>
<p><strong>Gudang:</strong> {$data['warehouse_name']}</p>
<p><strong>Customer:</strong> {$data['customer_name']}</p>
<p><strong>Tanggal:</strong> {$data['created_at']}</p>
<br>
<table border='1' cellpadding='5'>
<tr>
<th width='10%'>No</th>
<th width='20%'>Item Code</th>
<th width='35%'>Item Name</th>
<th width='15%'>Qty</th>
<th width='20%'>CBM</th>
</tr>
";

$no=1;
$totalQty=0;
$totalCbm=0;

while($row=mysqli_fetch_assoc($details)){
    $totalQty += $row['qty'];
    $totalCbm += $row['cbm'];

    $html .= "
    <tr>
        <td>{$no}</td>
        <td>{$row['item_code']}</td>
        <td>{$row['item_name']}</td>
        <td>{$row['qty']}</td>
        <td>{$row['cbm']}</td>
    </tr>
    ";
    $no++;
}

$html .= "
<tr>
<td colspan='3'><strong>TOTAL</strong></td>
<td><strong>$totalQty</strong></td>
<td><strong>$totalCbm</strong></td>
</tr>
</table>
";

$pdf->writeHTML($html);
$pdf->Output("DO_{$data['do_number']}.pdf",'I');
exit;