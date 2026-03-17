<?php
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=template_opening_stock.csv");

echo "item_code,qty\n";
echo "ITM-001,100\n";
echo "ITM-002,50\n";
echo "ITM-003,75\n";
exit;