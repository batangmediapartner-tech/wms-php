<?php

unset($_SESSION['do_import']);

header("Location:index.php?module=delivery_orders&action=create");
exit;