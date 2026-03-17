<?php
checkLogin();
checkRole(['ADMIN']);
global $conn;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($_FILES['file']['name'] == "") {
        echo "File belum dipilih.";
        exit;
    }

    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, "r");

    if ($handle === FALSE) {
        die("Gagal membaca file.");
    }

    mysqli_begin_transaction($conn);

    try {

        $rowNumber = 0;
        $inserted  = 0;

        while (($line = fgets($handle)) !== FALSE) {

            $rowNumber++;

            // Deteksi separator otomatis
            $delimiter = (substr_count($line, ';') > substr_count($line, ',')) ? ';' : ',';

            $data = str_getcsv($line, $delimiter);

            // Skip header
            if ($rowNumber == 1) {
                continue;
            }

            if (count($data) < 3) {
                continue;
            }

            $item_code = trim($data[0]);
            $item_name = trim($data[1]);
            $cbm       = trim($data[2]);

            if ($item_code == "" || $item_name == "" || $cbm == "") {
                continue;
            }

            if (!is_numeric($cbm)) {
                throw new Exception("CBM harus angka di baris $rowNumber");
            }

            $check = mysqli_query($conn, "
                SELECT id FROM items 
                WHERE item_code='$item_code' 
                AND is_deleted=0
            ");

            if (mysqli_num_rows($check) > 0) {
                continue; // skip duplicate
            }

            mysqli_query($conn, "
                INSERT INTO items (item_code, item_name, cbm, is_active)
                VALUES ('$item_code', '$item_name', '$cbm', 1)
            ");

            $inserted++;
        }

        mysqli_commit($conn);
        fclose($handle);

        echo "Import selesai.<br>";
        echo "Total data masuk: <b>$inserted</b><br><br>";
        echo "<a href='index.php?module=items&action=index'>Kembali</a>";

    } catch (Exception $e) {

        mysqli_rollback($conn);
        fclose($handle);

        echo "Import gagal: " . $e->getMessage();
    }
}
?>

<h2>Import Master Item (CSV)</h2>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file" accept=".csv" required><br><br>
    <button type="submit">Upload & Import</button>
</form>

<br>
<a href="index.php?module=items&action=index">Kembali</a>