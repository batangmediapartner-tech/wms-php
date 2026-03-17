<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$tmp=$_FILES['file']['tmp_name'];

$spreadsheet=IOFactory::load($tmp);

$data=$spreadsheet->getActiveSheet()->toArray();
?>

<form method="POST" action="process_import.php">

<table class="table">

<tr>
<th>Model</th>
<th>Qty</th>
<th>No DO</th>
<th>No PO</th>
</tr>

<?php
foreach($data as $i=>$row){

if($i==0) continue;
?>

<tr>

<td>
<input type="text" name="model[]" value="<?=$row[0]?>">
</td>

<td>
<input type="number" name="qty[]" value="<?=$row[1]?>">
</td>

<td>
<input type="text" name="no_do[]" value="<?=$row[2]?>">
</td>

<td>
<input type="text" name="no_po[]" value="<?=$row[3]?>">
</td>

</tr>

<?php } ?>

</table>

<button class="btn btn-success">Import</button>

</form>