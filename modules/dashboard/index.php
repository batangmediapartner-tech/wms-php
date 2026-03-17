<?php
checkLogin();
global $conn;

/* =========================
   PROSES REFRESH STOCK
========================= */

if(isset($_GET['refresh'])){

    mysqli_query($conn,"
        UPDATE stocks s
        JOIN (
            SELECT warehouse_id,item_id,SUM(qty_in-qty_out) real_qty
            FROM stock_cards
            GROUP BY warehouse_id,item_id
        ) x
        ON s.warehouse_id=x.warehouse_id
        AND s.item_id=x.item_id
        SET s.qty_available=x.real_qty
    ");

    mysqli_query($conn,"
        UPDATE stocks s
        JOIN items i ON s.item_id=i.id
        SET s.total_cbm=s.qty_available*i.cbm
    ");

    header("Location:index.php?module=dashboard&action=index");
    exit;
}

require BASE_PATH."/modules/layout/sidebar.php";

/* =========================
   SUMMARY DATA
========================= */

$totalItem = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) as t FROM items WHERE is_deleted=0
"))['t'];

$totalGudang = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) as t FROM warehouses
"))['t'];

$totalStock = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT SUM(qty_available) as t FROM stocks
"))['t'];

$totalCBM = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT SUM(s.qty_available * i.cbm) as t
    FROM stocks s
    LEFT JOIN items i ON s.item_id=i.id
"))['t'];


/* =========================
   GRAFIK 7 HARI
========================= */

$labels=[];
$grData=[];
$doData=[];

for($i=6;$i>=0;$i--){

    $date=date('Y-m-d',strtotime("-$i days"));
    $labels[]=date('d M',strtotime($date));

    $gr=mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT SUM(qty) as t
        FROM goods_receipt_details d
        JOIN goods_receipts g ON d.gr_id=g.id
        WHERE DATE(g.created_at)='$date'
        AND g.status='POSTED'
    "))['t'];

    $do=mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT SUM(qty) as t
        FROM delivery_order_details d
        JOIN delivery_orders o ON d.do_id=o.id
        WHERE DATE(o.created_at)='$date'
        AND o.status='POSTED'
    "))['t'];

    $grData[]=$gr?$gr:0;
    $doData[]=$do?$do:0;
}


/* =========================
   CBM PER GUDANG
========================= */

$warehouseCBM=mysqli_query($conn,"
    SELECT
        w.name,
        w.capacity_cbm,
        IFNULL(SUM(s.qty_available*i.cbm),0) as used_cbm
    FROM warehouses w
    LEFT JOIN stocks s ON w.id=s.warehouse_id
    LEFT JOIN items i ON s.item_id=i.id
    GROUP BY w.id
");

?>

<style>

.dashboard-grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:20px;
margin-bottom:20px;
}

.stat-card{
padding:20px;
border-radius:12px;
color:white;
font-weight:600;
box-shadow:0 4px 10px rgba(0,0,0,0.08);
}

.bg1{background:linear-gradient(135deg,#3b82f6,#2563eb);}
.bg2{background:linear-gradient(135deg,#10b981,#059669);}
.bg3{background:linear-gradient(135deg,#f59e0b,#d97706);}
.bg4{background:linear-gradient(135deg,#ef4444,#dc2626);}

.card{
background:white;
padding:20px;
border-radius:12px;
box-shadow:0 4px 10px rgba(0,0,0,0.05);
margin-bottom:20px;
}

.progress-bar{
height:20px;
border-radius:10px;
background:#e5e7eb;
overflow:hidden;
}

.progress-fill{
height:100%;
text-align:center;
color:white;
font-size:12px;
line-height:20px;
}

.refresh-btn{
background:#ef4444;
color:white;
padding:10px 16px;
border-radius:6px;
text-decoration:none;
font-weight:bold;
}

</style>

<div class="container-fluid">

<h2>Dashboard</h2>

<br>

<a class="refresh-btn"
href="index.php?module=dashboard&action=index&refresh=1"
onclick="return confirm('Refresh seluruh stock system?')">
REFRESH
</a>

<br><br>

<div class="dashboard-grid">

<div class="stat-card bg1">
Total Item<br><br>
<h2><?=number_format($totalItem)?></h2>
</div>

<div class="stat-card bg2">
Total Gudang<br><br>
<h2><?=number_format($totalGudang)?></h2>
</div>

<div class="stat-card bg3">
Total Stock<br><br>
<h2><?=number_format($totalStock)?></h2>
</div>

<div class="stat-card bg4">
Total Kubikasi<br><br>
<h2><?=number_format($totalCBM)?> CBM</h2>
</div>

</div>


<div class="card">
<h3>Barang Masuk vs Keluar (7 Hari)</h3>
<canvas id="movementChart" height="100"></canvas>
</div>


<div class="card">
<h3>Progress Kubikasi Gudang</h3>

<?php while($w=mysqli_fetch_assoc($warehouseCBM)){

$capacity=$w['capacity_cbm'] ?: 1;
$used=$w['used_cbm'];
$percent=min(100,round(($used/$capacity)*100));

if($percent<60){$color="#10b981";}
elseif($percent<85){$color="#f59e0b";}
else{$color="#ef4444";}

?>

<b><?=$w['name']?></b><br>
<?=number_format($used)?> / <?=number_format($capacity)?> CBM

<div class="progress-bar">
<div class="progress-fill"
style="width:<?=$percent?>%;background:<?=$color?>">
<?=$percent?>%
</div>
</div>

<br>

<?php } ?>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const ctx=document.getElementById('movementChart').getContext('2d');

new Chart(ctx,{
type:'line',
data:{
labels:<?=json_encode($labels)?>,
datasets:[
{
label:'Barang Masuk',
data:<?=json_encode($grData)?>,
borderColor:'#10b981',
backgroundColor:'rgba(16,185,129,0.1)',
tension:0.4
},
{
label:'Barang Keluar',
data:<?=json_encode($doData)?>,
borderColor:'#ef4444',
backgroundColor:'rgba(239,68,68,0.1)',
tension:0.4
}
]
},
options:{
responsive:true,
plugins:{legend:{position:'top'}},
scales:{y:{beginAtZero:true}}
}
});

</script>

<?php require BASE_PATH."/modules/layout/footer.php"; ?>