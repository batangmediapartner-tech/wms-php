<?php
checkLogin();
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

$warehouses=mysqli_query($conn,"SELECT * FROM warehouses WHERE is_deleted=0");
$items=mysqli_query($conn,"SELECT * FROM items WHERE is_deleted=0");

$number=generateTransferNumber($conn);
?>

<h2>Buat Transfer Stok</h2>

<form method="POST" action="index.php?module=stock_transfers&action=store">

Nomor
<input type="text" name="transfer_number" value="<?= $number ?>" readonly>

<br><br>

Dari Gudang
<select name="from_warehouse_id" required>
<?php while($w=mysqli_fetch_assoc($warehouses)){ ?>
<option value="<?= $w['id'] ?>"><?= $w['name'] ?></option>
<?php } ?>
</select>

<br><br>

Ke Gudang
<select name="to_warehouse_id" required>

<?php
$warehouses=mysqli_query($conn,"SELECT * FROM warehouses WHERE is_deleted=0");
while($w=mysqli_fetch_assoc($warehouses)){
?>

<option value="<?= $w['id'] ?>">
<?= $w['name'] ?>
</option>

<?php } ?>

</select>

<br><br>

Tanggal
<input type="date" name="transfer_date" value="<?= date("Y-m-d") ?>">

<br><br>

<table id="itemsTable">

<tr>
<th>Item</th>
<th>Qty</th>
<th></th>
</tr>

<tr>

<td>
<select name="item_id[]">
<?php while($i=mysqli_fetch_assoc($items)){ ?>
<option value="<?= $i['id'] ?>">
<?= $i['item_code'] ?> - <?= $i['item_name'] ?>
</option>
<?php } ?>
</select>
</td>

<td>
<input type="number" name="qty[]" required>
</td>

<td>
<button type="button" onclick="addRow()">+</button>
</td>

</tr>

</table>

<br>

<button class="btn btn-success">Simpan</button>

</form>

<script>

function addRow(){

var row=`
<tr>
<td>
<select name="item_id[]">
<?php
$items=mysqli_query($conn,"SELECT * FROM items WHERE is_deleted=0");
while($i=mysqli_fetch_assoc($items)){
echo "<option value='".$i['id']."'>".$i['item_code']." - ".$i['item_name']."</option>";
}
?>
</select>
</td>
<td><input type="number" name="qty[]"></td>
<td></td>
</tr>`;

document.getElementById("itemsTable").insertAdjacentHTML("beforeend",row);

}

</script>

</div></body></html>