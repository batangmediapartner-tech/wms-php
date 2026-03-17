<?php
$user = $_SESSION['username'] ?? 'User';
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>🏭 KBR - Warehouse Management System</title>

<style>
body{
    margin:0;
    font-family:Segoe UI, Tahoma, sans-serif;
    background:#f4f6f9;
}

/* ===== SIDEBAR ===== */
.sidebar{
    width:240px;
    height:100vh;
    position:fixed;
    top:0;
    left:0;
    background:linear-gradient(180deg,#1e293b,#0f172a);
    color:white;
    padding-top:20px;
    padding-bottom:40px;
    overflow-y:auto;
}

.sidebar h2{
    text-align:center;
    margin:0 0 25px 0;
    font-size:18px;
    font-weight:600;
    letter-spacing:.3px;
}

.sidebar a{
    display:block;
    color:#cbd5e1;
    padding:11px 20px;
    text-decoration:none;
    font-size:14px;
    border-left:4px solid transparent;
    transition:all .2s ease;
}

.sidebar a:hover{
    background:#1e40af;
    color:white;
    border-left:4px solid #3b82f6;
}

.sidebar .active{
    background:#2563eb;
    color:white;
    border-left:4px solid #60a5fa;
}

/* separator lebih rapi */
.sidebar hr{
    border:0;
    border-top:1px solid #334155;
    margin:10px 15px;
}

/* footer */
.sidebar-footer{
    position:absolute;
    bottom:8px;
    width:100%;
    text-align:center;
    font-size:11px;
    color:#94a3b8;
}

/* ===== TOPBAR ===== */
.topbar{
    background:white;
    padding:15px 20px;
    margin-left:240px;
    box-shadow:0 2px 6px rgba(0,0,0,0.08);
    font-size:14px;
}

/* ===== CONTENT ===== */
.content{
    margin-left:240px;
    padding:20px;
}

/* ===== CARD ===== */
.card{
    background:white;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 8px rgba(0,0,0,0.05);
    margin-bottom:20px;
}

/* ===== BUTTON ===== */
.btn{
    padding:6px 12px;
    border:none;
    border-radius:4px;
    cursor:pointer;
    text-decoration:none;
    font-size:13px;
}

.btn-primary{background:#2563eb;color:white;}
.btn-success{background:#16a34a;color:white;}
.btn-warning{background:#f59e0b;color:white;}
.btn-danger{background:#dc2626;color:white;}

/* ===== TABLE ===== */
table{
    width:100%;
    border-collapse:collapse;
}

table th,table td{
    border:1px solid #ddd;
    padding:8px;
    font-size:13px;
}

table th{
    background:#f1f5f9;
}
</style>
</head>

<body>

<div class="sidebar">
<h2>WH KBR X SEID</h2>

<a href="index.php?module=dashboard&action=index">Dashboard</a>

<hr>

<a href="index.php?module=items&action=index">Barang</a>
<a href="index.php?module=warehouses&action=index">Gudang</a>
<a href="index.php?module=customers&action=index">Customer</a>
<a href="index.php?module=manifests&action=index">Manifest</a>
<a href="index.php?module=users&action=index">User</a>

<hr>

<a href="index.php?module=goods_receipts&action=index">Incoming</a>
<a href="index.php?module=delivery_orders&action=index">Outgoing</a>
<a href="index.php?module=surat_jalan&action=index">Surat Jalan</a>
<a href="index.php?module=stock_transfers&action=index">Transfer Stok</a>
<a href="index.php?module=opening_stock&action=index">Stok Awal</a>

<hr>

<a href="index.php?module=reports&action=stock">Stok</a>
<a href="index.php?module=reports&action=stock_card">Kartu Stok</a>

<hr>

<a href="index.php?module=period_lock&action=index">Kunci Periode</a>
<a href="index.php?module=activity_logs&action=index">Histori</a>

<hr>

<a href="index.php?module=auth&action=logout">Logout</a>

</div>

<div class="topbar">
Welcome, <b><?= $user ?></b> | Role: <?= $role ?>
</div>

<div class="content">