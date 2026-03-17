<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

/* =========================
   IMPORT VIA CSV
========================= */

if(isset($_POST['import'])){

    $warehouse_id = $_POST['warehouse_id'];
    $date         = $_POST['date'];

    if(!isset($_FILES['file']['tmp_name'])){
        die("File tidak ditemukan.");
    }

    mysqli_begin_transaction($conn);

    try{

        // Cek sudah pernah opening
        $cekOpening = mysqli_fetch_assoc(mysqli_query($conn,"
            SELECT COUNT(*) as t FROM opening_batches
            WHERE warehouse_id='$warehouse_id'
        "));

        if($cekOpening['t'] > 0){
            throw new Exception("Opening sudah pernah dilakukan untuk gudang ini.");
        }

        // Cek sudah ada transaksi?
        $cekTransaksi = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT 
        (SELECT COUNT(*) FROM goods_receipts WHERE warehouse_id='$warehouse_id') +
        (SELECT COUNT(*) FROM delivery_orders WHERE warehouse_id='$warehouse_id')
        AS total
        "));

        if($cekTransaksi['total'] > 0){
            throw new Exception("Tidak bisa opening karena sudah ada transaksi.");
        }

        $batchNumber = "OPEN/".date('YmdHis');

        mysqli_query($conn,"
            INSERT INTO opening_batches
            (batch_number,warehouse_id,created_by)
            VALUES
            ('$batchNumber','$warehouse_id','".$_SESSION['user_id']."')
        ");

        $batchId = mysqli_insert_id($conn);

        $file = fopen($_FILES['file']['tmp_name'], 'r');

        $header = fgetcsv($file);

        if($header[0] != 'item_code' || $header[1] != 'qty'){
            throw new Exception("Format header salah. Harus: item_code,qty");
        }

        while(($row = fgetcsv($file)) !== FALSE){

            $item_code = mysqli_real_escape_string($conn,$row[0]);
            $qty = (int)$row[1];

            if($qty < 0){
                throw new Exception("Qty tidak boleh minus.");
            }

            $item = mysqli_fetch_assoc(mysqli_query($conn,"
                SELECT * FROM items WHERE item_code='$item_code'
            "));

            if(!$item){
                throw new Exception("Item tidak ditemukan: ".$item_code);
            }

            $total_cbm = $item['cbm'] * $qty;

            mysqli_query($conn,"
                INSERT INTO stocks
                (warehouse_id,item_id,qty_available,total_cbm)
                VALUES
                ('$warehouse_id','".$item['id']."','$qty','$total_cbm')
            ");

            mysqli_query($conn,"
                INSERT INTO stock_cards
                (warehouse_id,item_id,transaction_type,reference_number,qty_in,qty_out,balance_after,created_at,opening_batch_id)
                VALUES
                ('$warehouse_id','".$item['id']."','OPENING','$batchNumber','$qty',0,'$qty','$date','$batchId')
            ");
        }

        fclose($file);

        logActivity('Opening Stock','IMPORT','Import Opening Batch '.$batchNumber);

        mysqli_commit($conn);

        echo "<div class='card' style='background:#dcfce7;color:#166534;'>
        Import Opening berhasil. Batch: $batchNumber
        </div>";

    }catch(Exception $e){

        mysqli_rollback($conn);

        echo "<div class='card' style='background:#fee2e2;color:#991b1b;'>
        ".$e->getMessage()."
        </div>";
    }
}

/* =========================
   DATA
========================= */

$warehouses = mysqli_query($conn,"SELECT * FROM warehouses");
?>

<h2>Import Opening Stock via Excel (CSV)</h2>

<div class="card">

<form method="POST" enctype="multipart/form-data">

<label>Tanggal Opening</label><br>
<input type="date" name="date" required value="<?= date('Y-m-d'); ?>">

<br><br>

<label>Gudang</label><br>
<select name="warehouse_id" required>
<option value="">-- Pilih Gudang --</option>
<?php while($w=mysqli_fetch_assoc($warehouses)){ ?>
<option value="<?= $w['id']; ?>"><?= $w['name']; ?></option>
<?php } ?>
</select>

<br><br>

<label>Upload File (CSV)</label><br>
<input type="file" name="file" accept=".csv" required>

<br><br>

<button type="submit" name="import" class="btn btn-primary">
Import Opening Stock
</button>

<a class="btn btn-warning"
href="index.php?module=opening_stock&action=template">
Download Template CSV
</a>

</form>

<br>

<b>Format CSV:</b><br>
item_code,qty<br>
ITM-001,100<br>
ITM-002,50

</div>

</div></body></html>