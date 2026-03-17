<?php
checkLogin();
require BASE_PATH."/modules/layout/sidebar.php";
?>

<h2>Import Excel Surat Jalan</h2>

<form action="preview_import.php" method="POST" enctype="multipart/form-data">

<input type="file" name="file" required>

<button class="btn btn-primary">Upload</button>

</form>