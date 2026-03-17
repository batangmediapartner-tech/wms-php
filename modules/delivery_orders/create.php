<?php
checkLogin();
checkRole(['ADMIN','WAREHOUSE']);
global $conn;

require BASE_PATH."/modules/layout/sidebar.php";
require BASE_PATH."/modules/layout/footer.php";

/* =========================
   PROSES SIMPAN DO
========================= */
if(isset($_POST['save'])){

    $warehouse_id = $_POST['warehouse_id'];
    $customer_id  = $_POST['customer_id'];

    mysqli_begin_transaction($conn);

    try{

        $today = date('Ymd');

$q = mysqli_query($conn,"
    SELECT MAX(do_number) as last_number
    FROM delivery_orders
    WHERE do_number LIKE 'SJ-$today-%'
");

$data = mysqli_fetch_assoc($q);

if($data['last_number']){
    $last = explode('-', $data['last_number']);
    $urut = (int)$last[2] + 1;
}else{
    $urut = 1;
}

$urut = str_pad($urut,4,'0',STR_PAD_LEFT);

$number = "SJ-$today-$urut";

        mysqli_query($conn,"
            INSERT INTO delivery_orders
            (do_number,warehouse_id,customer_id,status,created_by)
            VALUES
            ('$number','$warehouse_id','$customer_id','POSTED','".$_SESSION['user_id']."')
        ");

        $do_id = mysqli_insert_id($conn);

        foreach($_POST['item_id'] as $key=>$item_id){

            $qty = (int)$_POST['qty'][$key];
            if($qty <= 0) continue;

            $stock = mysqli_fetch_assoc(mysqli_query($conn,"
                SELECT qty_available
                FROM stocks
                WHERE warehouse_id='$warehouse_id'
                AND item_id='$item_id'
            "));

            if(!$stock || $stock['qty_available'] < $qty){
                throw new Exception("Stok tidak cukup.");
            }

            $item = mysqli_fetch_assoc(mysqli_query($conn,"
                SELECT cbm FROM items WHERE id='$item_id'
            "));

            $total_cbm = $item['cbm'] * $qty;

            mysqli_query($conn,"
                INSERT INTO delivery_order_details
                (do_id,item_id,qty,cbm)
                VALUES
                ('$do_id','$item_id','$qty','$total_cbm')
            ");

            mysqli_query($conn,"
                UPDATE stocks
                SET qty_available = qty_available - $qty,
                    total_cbm = total_cbm - $total_cbm
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
                ('$warehouse_id','$item_id','DO','$number',0,'$qty','$balance')
            ");
        }

        logActivity('Delivery Order','CREATE','Buat DO '.$number);

        mysqli_commit($conn);
		
		unset($_SESSION['do_import']);

        header("Location:index.php?module=delivery_orders&action=index");
        exit;

    } catch(Exception $e){
        mysqli_rollback($conn);
        echo "<div class='card' style='background:#fee2e2;color:#991b1b;'>
              Gagal: ".$e->getMessage()."
              </div>";
    }
}

/* =========================
   DATA
========================= */
$warehouses = mysqli_query($conn,"SELECT * FROM warehouses");
$customers  = mysqli_query($conn,"SELECT * FROM customers WHERE is_active=1 ORDER BY customer_name");
?>

<h2>Buat Delivery Order</h2>

<div class="card">

<!-- =========================
   IMPORT EXCEL
========================= -->

<form method="POST"
action="index.php?module=delivery_orders&action=import_items"
enctype="multipart/form-data">

<input type="file" name="excel" required>

<button class="btn btn-success">
Upload Excel
</button>

</form>

<br>

<?php
if(isset($_SESSION['do_import'])){
foreach($_SESSION['do_import'] as $i){
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
<select name="warehouse_id" id="warehouse" required>
<option value="">-- Pilih Gudang --</option>
<?php while($w=mysqli_fetch_assoc($warehouses)){ ?>
<option value="<?= $w['id']; ?>"><?= $w['name']; ?></option>
<?php } ?>
</select>

<br><br>

<label>Customer</label><br>
<select name="customer_id" id="customerSelect" required style="width:300px;">
<option value="">-- Pilih Customer --</option>
<?php while($c=mysqli_fetch_assoc($customers)){ ?>
<option value="<?= $c['id']; ?>"><?= $c['customer_name']; ?></option>
<?php } ?>
</select>

<br><br>

<table id="itemTable">
<tr>
    <th style="width:50%">Item</th>
    <th>Stok</th>
    <th>Qty</th>
    <th></th>
</tr>
</table>

<br>

<button type="button" class="btn btn-primary" onclick="addRow()">
+ Tambah Item
</button>

<br><br>

<a href="?module=delivery_orders&action=reset_import" class="btn btn-danger">
Reset Import
</a>

<button type="submit" name="save" class="btn btn-primary">
Simpan DO
</button>

</form>
</div>

<!-- SELECT2 CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

function addRowImport(id,code,qty){

    let table = document.getElementById('itemTable');
    let row = table.insertRow();

    let cell1 = row.insertCell(0);
    let cell2 = row.insertCell(1);
    let cell3 = row.insertCell(2);
    let cell4 = row.insertCell(3);

    cell1.innerHTML = `
        <select name="item_id[]">
            <option value="${id}" selected>${code}</option>
        </select>
    `;

    if(stockData[id]){
        cell2.innerHTML = stockData[id].qty;
    }else{
        cell2.innerHTML = 0;
    }

    cell3.innerHTML = `
        <input type="number" name="qty[]" value="${qty}" min="1">
    `;

    cell4.innerHTML = `
        <button type="button" onclick="this.parentNode.parentNode.remove()" class="btn btn-danger">X</button>
    `;
}

let stockData = {};

$('#warehouse').on('change', function(){
    let wh = this.value;
    if(!wh) return;

    fetch('index.php?module=delivery_orders&action=get_stock&warehouse_id='+wh)
    .then(res=>res.json())
    .then(data=>{
        stockData = data;
    });
});

function addRow(){

    if(Object.keys(stockData).length === 0){
        alert("Pilih gudang terlebih dahulu.");
        return;
    }

    let table = document.getElementById('itemTable');
    let row = table.insertRow();

    let cell1 = row.insertCell(0);
    let cell2 = row.insertCell(1);
    let cell3 = row.insertCell(2);
    let cell4 = row.insertCell(3);

    let select = document.createElement('select');
    select.name = "item_id[]";
    select.className = "item-select";
    select.required = true;
    select.style.width = "100%";

    select.innerHTML = '<option value="">-- Pilih Item --</option>';

    for(let id in stockData){
        select.innerHTML += '<option value="'+id+'">'+
        stockData[id].item_code+' (Stok: '+stockData[id].qty+')'+
        '</option>';
    }

    cell1.appendChild(select);

    $(select).select2();

    select.onchange = function(){
        let id = this.value;
        if(stockData[id]){
            cell2.innerHTML = stockData[id].qty;
        }else{
            cell2.innerHTML = '';
        }
    };

    let qty = document.createElement('input');
    qty.type = 'number';
    qty.name = 'qty[]';
    qty.min = 1;
    qty.required = true;

    cell3.appendChild(qty);

    let btn = document.createElement('button');
    btn.type='button';
    btn.innerHTML='X';
    btn.className='btn btn-danger';
    btn.onclick=function(){ row.remove(); };

    cell4.appendChild(btn);
}

</script>
<script>
// ===== LIVE SEARCH CUSTOMER (SAFE) =====
$(document).ready(function(){
    $('#customerSelect').select2({
        placeholder: "-- Pilih Customer --",
        allowClear: true,
        width: '300px'
    });
});
</script>
</div>
</body>
</html>