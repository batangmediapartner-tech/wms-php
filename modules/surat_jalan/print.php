<?php

checkLogin();
global $conn;

$id=$_GET['id'];

$data=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT sj.*,c.customer_name
FROM surat_jalan sj
LEFT JOIN customers c ON sj.customer_id=c.id
WHERE sj.id='$id'
"));

$items=mysqli_query($conn,"
SELECT * FROM surat_jalan_details
WHERE sj_id='$id'
");

?>

<pre>

PT KIBAR RAYA LOGISTIK
JL SEGARAN 3/5 SEMARANG
TELP : 024-7649032

----------------------------------------------------

SURAT JALAN

No SJ    : <?=$data['sj_number']?>

Tanggal  : <?=$data['tanggal']?>

Customer : <?=$data['customer_name']?>

----------------------------------------------------

No  Model                 Qty   No DO     No PO
----------------------------------------------------

<?php
$no=1;

while($d=mysqli_fetch_assoc($items)){

echo str_pad($no,4).
str_pad($d['model'],22).
str_pad($d['qty'],6).
str_pad($d['no_do'],10).
str_pad($d['no_po'],10)."\n";

$no++;

}
?>

----------------------------------------------------

Pengirim                 Penerima

</pre>

<script>
window.print();
</script>