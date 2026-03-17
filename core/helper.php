<?php

function logActivity($module, $action, $description){
    global $conn;

    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

    mysqli_query($conn,"
        INSERT INTO activity_logs
        (user_id,module,action,description)
        VALUES
        ('$user_id','$module','$action','$description')
    ");
}

function checkPeriodLock($date = null){
    global $conn;

    if(!$date){
        $date = date('Y-m-d');
    }

    $year  = date('Y', strtotime($date));
    $month = date('m', strtotime($date));

    $query = mysqli_query($conn,"
        SELECT * FROM period_locks
        WHERE year='$year' 
        AND month='$month' 
        AND is_locked=1
    ");

    if(mysqli_num_rows($query) > 0){
        die("Periode $month/$year sudah dikunci. Transaksi tidak diperbolehkan.");
    }
}

function generateTransferNumber($conn){

$year = date('Y');

$q = mysqli_query($conn,"
SELECT MAX(id) as last_id 
FROM stock_transfers
");

$d = mysqli_fetch_assoc($q);
$next = $d['last_id'] + 1;

return "WT-$year-".str_pad($next,4,"0",STR_PAD_LEFT);

}