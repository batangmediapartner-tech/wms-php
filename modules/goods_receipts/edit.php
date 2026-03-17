<?php
checkLogin();
checkRole(['ADMIN','WAREHOUSE']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";

$id = $_GET['id'];

$gr = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT * FROM goods_receipts WHERE id='$id'
"));

if(!$gr){
    die("GR tidak ditemukan.");
}

if($gr['status'] != 'POSTED'){
    die("GR tidak bisa diedit.");
}

/* =========================
   PROSES UPDATE
========================= */
if(isset($_POST['update'])){

    $warehouse_id = $gr['warehouse_id'];

    mysqli_begin_transaction($conn);

    try{

        // 1️⃣ Reverse stok lama
        $old_details = mysqli_query($conn,"
            SELECT * FROM goods_receipt_details
            WHERE gr_id='$id'
        ");

        while($old=mysqli_fetch_assoc($old_details)){

            mysqli_query($conn,"
                UPDATE stocks
                SET qty_available = qty_available - ".$old['qty'].",
                    total_cbm = total_cbm - ".$old['cbm']."
                WHERE warehouse_id='$warehouse_id'
                AND item_id='".$old['item_id']."'
            ");
        }

        // 2️⃣ Hapus detail lama
        mysqli_query($conn,"
            DELETE FROM goods_receipt_details
            WHERE gr_id='$id'
        ");

        // 3️⃣ Insert detail baru
        foreach($_POST['item_id'] as $key=>$item_id){

            $qty = (int)$_POST['qty'][$key];
            if($qty <= 0) continue;

            $item = mysqli_fetch_assoc(mysqli_query($conn,"
                SELECT cbm FROM items WHERE id='$item_id'
            "));

            $total_cbm = $item['cbm'] * $qty;

            mysqli_query($conn,"
                INSERT INTO goods_receipt_details
                (gr_id,item_id,qty,cbm)
                VALUES
                ('$id','$item_id','$qty','$total_cbm')
            ");

            mysqli_query($conn,"
                UPDATE stocks
                SET qty_available = qty_available + $qty,
                    total_cbm = total_cbm + $total_cbm
                WHERE warehouse_id='$warehouse_id'
                AND item_id='$item_id'
            ");

            $balance = mysqli_fetch_assoc(mysqli_query($conn,"
                SELECT qty_available FROM stocks
                WHERE warehouse_id='$warehouse_id'
                AND item_id='$item_id'
            "))['qty_available'];

            mysqli_query($conn,"
                INSERT INTO stock_cards
                (warehouse_id,item_id,transaction_type,reference_number,qty_in,qty_out,balance_after)
                VALUES
                ('$warehouse_id','$item_id','EDIT_GR','".$gr['gr_number']."','$qty',0,'$balance')
            ");
        }

        logActivity('Goods Receipt','EDIT','Edit GR '.$gr['gr_number']);

        mysqli_commit($conn);

        header("Location:index.php?module=goods_receipts&action=detail&id=$id");
        exit;

    } catch(Exception $e){

        mysqli_rollback($conn);
        echo "Gagal: ".$e->getMessage();
    }
}

/* =========================
   LOAD DETAIL
========================= */

$details = mysqli_query($conn,"
    SELECT d.*, i.item_code
    FROM goods_receipt_details d
    JOIN items i ON d.item_id=i.id
    WHERE d.gr_id='$id'
");
?>

<h2>Edit Goods Receipt</h2>

<div class="card">

<form method="POST">

<table id="itemTable">
<tr>
<th>Item</th>
<th>Qty</th>
<th></th>
</tr>

<?php while($row=mysqli_fetch_assoc($details)){ ?>
<tr>
<td>
<select name="item_id[]" required>
<?php
$items = mysqli_query($conn,"SELECT * FROM items WHERE is_active=1");
while($i=mysqli_fetch_assoc($items)){
$selected = ($i['id']==$row['item_id'])?'selected':'';
echo "<option value='".$i['id']."' $selected>".$i['item_code']."</option>";
}
?>
</select>
</td>

<td>
<input type="number" name="qty[]" value="<?= $row['qty']; ?>" required min="1">
</td>

<td>
<button type="button" class="btn btn-danger"
onclick="this.closest('tr').remove()">X</button>
</td>
</tr>
<?php } ?>

</table>

<br>

<button type="button" class="btn btn-primary"
onclick="addRow()">+ Tambah Item</button>

<br><br>

<button type="submit" name="update"
class="btn btn-primary">
Update GR
</button>

</form>

</div>

<script>
function addRow(){

let table = document.getElementById('itemTable');
let row = table.insertRow();

row.innerHTML = `
<td>
<select name="item_id[]" required>
<?php
$items2 = mysqli_query($conn,"SELECT * FROM items WHERE is_active=1");
while($i2=mysqli_fetch_assoc($items2)){
echo "<option value='".$i2['id']."'>".$i2['item_code']."</option>";
}
?>
</select>
</td>
<td>
<input type="number" name="qty[]" required min="1">
</td>
<td>
<button type="button" class="btn btn-danger"
onclick="this.closest('tr').remove()">X</button>
</td>
`;
}
</script>

</div></body></html>