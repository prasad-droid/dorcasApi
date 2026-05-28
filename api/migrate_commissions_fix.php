<?php
require_once __DIR__ . '/config/db.php';

// Fix past payments where ccavenue_handler.php only updated bookings
$conn->query("UPDATE vendor_payments vp JOIN bookings b ON vp.booking_id = b.id SET vp.status = 'paid' WHERE b.commission_status = 'paid' AND vp.status = 'pending'");
echo $conn->affected_rows . " vendor_payments records fixed.<br>";

// Ensure any mismatch where vendor-tracking only updated vendor_payments gets synced back to bookings
$conn->query("UPDATE bookings b JOIN vendor_payments vp ON vp.booking_id = b.id SET b.commission_status = vp.status, b.commission_amount = vp.amount WHERE b.commission_status != vp.status OR b.commission_amount != vp.amount");
echo $conn->affected_rows . " bookings records synced.<br>";

echo "DONE.";
