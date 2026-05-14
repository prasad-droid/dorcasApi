<?php
require_once "api/config/db.php";
$res = $conn->query("DESCRIBE vendor_payments");
if ($res) {
    while($row = $res->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Table vendor_payments does not exist";
}
?>
