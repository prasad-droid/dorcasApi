<?php
require_once __DIR__ . "/../api/config/db.php";
$res = $conn->query("SELECT id, status, vendor_id FROM bookings ORDER BY id DESC LIMIT 5");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
