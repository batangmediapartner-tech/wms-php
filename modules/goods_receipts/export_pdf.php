<?php
ob_start();

require_once BASE_PATH."/library/tcpdf/tcpdf.php";
global $conn;

$value = $_GET['id'] ?? '';

if(!$value){
    die("Parameter tidak ditemukan");
}

/* =====================================
   DETECT APA YANG DIKIRIM (ID atau GR NUMBER)
===================================== */

if(is_numeric($value)){
    // Cari berdasarkan ID
    $where = "g.id = '$value'";
}else{
    // Cari berdasarkan GR Number
    $where = "g.gr_number = '$value'";
}

/* =====================================
   HEADER DATA
===================================== */

$dataQuery = mysqli_query($conn,"
SELECT g.*, 
       w.name AS warehouse_name,
       m.manifest_number
FROM goods_receipts g
LEFT JOIN warehouses w ON g.warehouse_id = w.id
LEFT JOIN manifests m ON g.manifest_id = m.id
WHERE $where
");

$data = mysqli_fetch_assoc($dataQuery);

if(!$data){
    die("Data GR tidak ditemukan. Value dikirim: ".$value);
}

/* =====================================
   DETAIL DATA
===================================== */

$details = mysqli_query($conn,"
SELECT gd.*, i.item_code, i.item_name
FROM goods_receipt_details gd
JOIN items i ON gd.item_id = i.id
WHERE gd.gr_id = '{$data['id']}'
");

/* =====================================
   PDF SETUP
===================================== */

$pdf = new TCPDF();
$pdf->SetCreator('WMS');
$pdf->SetAuthor('WMS KBR');
$pdf->SetTitle('Goods Receipt');
$pdf->AddPage();
$pdf->SetFont('helvetica','',10);

$html = "
<h2 style='text-align:center;'>GOODS RECEIPT</h2>
<hr>
<p><strong>No GR:</strong> {$data['gr_number']}</p>
<p><strong>Gudang:</strong> ".($data['warehouse_name'] ?? '-')."</p>
<p><strong>Manifest:</strong> ".($data['manifest_number'] ?? '-')."</p>
<p><strong>Tanggal:</strong> {$data['created_at']}</p>
<br>
<table border='1' cellpadding='6'>
<tr style='background-color:#f2f2f2;'>
<th width='10%'>No</th>
<th width='25%'>Item Code</th>
<th width='35%'>Item Name</th>
<th width='15%'>Qty</th>
<th width='15%'>CBM</th>
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
<td><strong>{$totalQty}</strong></td>
<td><strong>{$totalCbm}</strong></td>
</tr>
</table>
<br><br>
<p>Dicetak pada: ".date('d-m-Y H:i')."</p>
";

$pdf->writeHTML($html);

ob_end_clean();

$pdf->Output("GR_{$data['gr_number']}.pdf",'I');
exit;