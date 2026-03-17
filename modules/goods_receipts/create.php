<?php
checkLogin();
checkRole(['ADMIN','WAREHOUSE']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

/* =========================
   PROSES SIMPAN GR
========================= */
if(isset($_POST['save'])){

    $warehouse_id = $_POST['warehouse_id'];
    $manifest_id  = $_POST['manifest_id'];

    mysqli_begin_transaction($conn);

    try{

        $today = date('Ymd');

$q = mysqli_query($conn,"
SELECT MAX(gr_number) as last_number
FROM goods_receipts
WHERE gr_number LIKE 'GR-$today-%'
");

$data = mysqli_fetch_assoc($q);

if($data['last_number']){
    $last = explode('-', $data['last_number']);
    $urut = (int)$last[2] + 1;
}else{
    $urut = 1;
}

$urut = str_pad($urut,4,'0',STR_PAD_LEFT);

$number = "GR-$today-$urut";

        mysqli_query($conn,"
            INSERT INTO goods_receipts
            (gr_number,warehouse_id,manifest_id,status,created_by)
            VALUES
            ('$number','$warehouse_id','$manifest_id','POSTED','".$_SESSION['user_id']."')
        ");

        $gr_id = mysqli_insert_id($conn);

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
                ('$gr_id','$item_id','$qty','$total_cbm')
            ");

            $check = mysqli_fetch_assoc(mysqli_query($conn,"
                SELECT id FROM stocks
                WHERE warehouse_id='$warehouse_id'
                AND item_id='$item_id'
            "));

            if($check){
                mysqli_query($conn,"
                    UPDATE stocks
                    SET qty_available = qty_available + $qty,
                        total_cbm = total_cbm + $total_cbm
                    WHERE warehouse_id='$warehouse_id'
                    AND item_id='$item_id'
                ");
            }else{
                mysqli_query($conn,"
                    INSERT INTO stocks
                    (warehouse_id,item_id,qty_available,total_cbm)
                    VALUES
                    ('$warehouse_id','$item_id','$qty','$total_cbm')
                ");
            }

            $balance = mysqli_fetch_assoc(mysqli_query($conn,"
                SELECT qty_available FROM stocks
                WHERE warehouse_id='$warehouse_id'
                AND item_id='$item_id'
            "))['qty_available'];

            mysqli_query($conn,"
                INSERT INTO stock_cards
                (warehouse_id,item_id,transaction_type,reference_number,qty_in,qty_out,balance_after)
                VALUES
                ('$warehouse_id','$item_id','GR','$number','$qty',0,'$balance')
            ");
        }

        logActivity('Goods Receipt','CREATE','Buat GR '.$number);

        mysqli_commit($conn);
		
		unset($_SESSION['gr_import']);
		
		mysqli_query($conn,"
UPDATE manifests SET status='CLOSED'
WHERE id='$manifest_id'
");

        header("Location:index.php?module=goods_receipts&action=index");
        exit;

    } catch(Exception $e){

        mysqli_rollback($conn);

        echo "<div class='card' style='background:#fee2e2;color:#991b1b;'>
              Gagal: ".$e->getMessage()."
              </div>";
    }
}

/* =========================
   DATA AWAL
========================= */
$warehouses = mysqli_query($conn,"SELECT * FROM warehouses");
$manifests  = mysqli_query($conn,"SELECT * FROM manifests WHERE status='OPEN'");
?>

<h2>Buat Goods Receipt</h2>

<div class="card">

<!-- =========================
   IMPORT EXCEL
========================= -->

<form method="POST"
action="index.php?module=goods_receipts&action=import_items"
enctype="multipart/form-data">

<input type="file" name="excel" required>

<button class="btn btn-success">
Upload Excel
</button>

</form>

<br>

<!-- =========================
   PREVIEW HASIL IMPORT
========================= -->

<?php
if(isset($_SESSION['gr_import'])){
foreach($_SESSION['gr_import'] as $i){
?>

<script>
window.addEventListener('load',function(){

addRowImport(
'<?= $i['id'] ?>',
'<?= $i['code'] ?>',
'<?= $i['qty'] ?>'
);

});
</script>

<?php
}
}
?>

<form method="POST">

<label>Gudang</label><br>
<select name="warehouse_id" required>
<option value="">-- Pilih Gudang --</option>
<?php while($w=mysqli_fetch_assoc($warehouses)){ ?>
<option value="<?= $w['id']; ?>"><?= $w['name']; ?></option>
<?php } ?>
</select>

<br><br>

<label>Manifest</label><br>
<select name="manifest_id" required>
<option value="">-- Pilih Manifest --</option>
<?php while($m=mysqli_fetch_assoc($manifests)){ ?>
<option value="<?= $m['id']; ?>"><?= $m['manifest_number']; ?></option>
<?php } ?>
</select>

<br><br>

<table id="itemTable">
<tr>
<th style="width:50%">Item</th>
<th>Qty</th>
<th></th>
</tr>
</table>

<br>

<button type="button" class="btn btn-primary" onclick="addRow()">
+ Tambah Item
</button>

<br><br>

<a href="?module=goods_receipts&action=reset_import" class="btn btn-danger">
Reset Import
</a>

<button type="submit" name="save" class="btn btn-primary">
Simpan GR
</button>

</form>
</div>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

function addRow(){

    let table = document.getElementById('itemTable');
    let row = table.insertRow();

    let cell1 = row.insertCell(0);
    let cell2 = row.insertCell(1);
    let cell3 = row.insertCell(2);

    let select = document.createElement('select');
    select.name = "item_id[]";
    select.required = true;
    select.style.width="100%";

    select.innerHTML = `
        <option value="">-- Pilih Item --</option>
        <?php
        $items = mysqli_query($conn,"SELECT * FROM items WHERE is_active=1");
        while($i=mysqli_fetch_assoc($items)){
            echo "<option value='".$i['id']."'>".$i['item_code']."</option>";
        }
        ?>
    `;

    cell1.appendChild(select);
    $(select).select2();

    let qty = document.createElement('input');
    qty.type = 'number';
    qty.name = 'qty[]';
    qty.min = 1;
    qty.required = true;
    cell2.appendChild(qty);

    let btn = document.createElement('button');
    btn.type='button';
    btn.innerHTML='X';
    btn.className='btn btn-danger';
    btn.onclick=function(){ row.remove(); };
    cell3.appendChild(btn);
}

function addRowImport(id,code,qty){

    let table = document.getElementById('itemTable');
    let row = table.insertRow();

    let cell1 = row.insertCell(0);
    let cell2 = row.insertCell(1);
    let cell3 = row.insertCell(2);

    cell1.innerHTML = `
        <select name="item_id[]">
            <option value="${id}" selected>${code}</option>
        </select>
    `;

    cell2.innerHTML = `
        <input type="number" name="qty[]" value="${qty}">
    `;

    cell3.innerHTML = `
        <button type="button" onclick="this.parentNode.parentNode.remove()" class="btn btn-danger">X</button>
    `;
}

</script>

</div>
</body>
</html>