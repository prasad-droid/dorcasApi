<?php
require_once 'config/db.php';

$sql1 = "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS commission_status ENUM('pending', 'paid', 'not_applicable') DEFAULT 'pending'";
$sql2 = "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS commission_amount DECIMAL(10,2) DEFAULT 0.00";
$sql3 = "ALTER TABLE ccav_orders ADD COLUMN IF NOT EXISTS tracking_id VARCHAR(100) DEFAULT NULL";
$sql4 = "ALTER TABLE ccav_orders ADD COLUMN IF NOT EXISTS bank_ref_no VARCHAR(100) DEFAULT NULL";
$sql5 = "ALTER TABLE ccav_orders ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'Started'";

if ($conn->query($sql1) && $conn->query($sql2) && $conn->query($sql3) && $conn->query($sql4) && $conn->query($sql5)) {
    echo "Migration successful: columns added.";
} else {
    echo "Migration failed: " . $conn->error;
}
?>
