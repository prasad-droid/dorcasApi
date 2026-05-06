<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/fcm.php";

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];
$user_id = $user['id'];

if ($role !== 'technician') {
    sendResponse(false, "Only technicians can complete jobs");
}

$input = json_decode(file_get_contents("php://input"), true);
$job_id = $input['job_id'] ?? null;

if (!$job_id) {
    sendResponse(false, "Job ID is required");
}

// Temporary Migration: Add completed_at to bookings if not present (Cross-version compatible)
$check_col = $conn->query("SHOW COLUMNS FROM bookings LIKE 'completed_at'");
if ($check_col->num_rows == 0) {
    $conn->query("ALTER TABLE bookings ADD COLUMN completed_at datetime DEFAULT NULL");
}

// 1. Fetch booking and service price
$query = "
    SELECT b.*, s.service_price 
    FROM bookings b 
    LEFT JOIN services s ON b.service_id = s.id 
    WHERE b.id = ? AND b.vendor_id = ? AND b.status = 'ongoing' 
    LIMIT 1
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $job_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    sendResponse(false, "Active job not found or already completed");
}

$amount = (float)($booking['service_price'] ?? 0);

$conn->begin_transaction();

try {
    // 2. Update booking status and record amount
    $commission_rate = 0.10; // 10% commission
    $commission_amount = $amount * $commission_rate;
    
    $update_stmt = $conn->prepare("UPDATE bookings SET status = 'completed', completed_at = NOW(), amount_paid = ?, commission_amount = ?, commission_status = 'pending' WHERE id = ?");
    $update_stmt->bind_param("ddi", $amount, $commission_amount, $job_id);
    $update_stmt->execute();

    // 3. Ensure vendor has a wallet and update balance
    // First, check if wallet exists
    $wallet_check = $conn->prepare("SELECT id FROM vendor_wallet WHERE vendor_id = ?");
    $wallet_check->bind_param("i", $user_id);
    $wallet_check->execute();
    if (!$wallet_check->get_result()->fetch_assoc()) {
        $create_wallet = $conn->prepare("INSERT INTO vendor_wallet (vendor_id, balance) VALUES (?, 0)");
        $create_wallet->bind_param("i", $user_id);
        $create_wallet->execute();
    }

    $wallet_stmt = $conn->prepare("UPDATE vendor_wallet SET balance = balance + ? WHERE vendor_id = ?");
    $wallet_stmt->bind_param("di", $amount, $user_id);
    $wallet_stmt->execute();

    $conn->commit();

    // 5. Notify customer
    $msg = "Your service for " . ($booking['service_name'] ?? "your request") . " has been completed. Please leave a review!";
    createNotification($conn, $booking['customer_id'], 'customer', 'booking_completed', 'Service Completed', $msg, $job_id);

    sendResponse(true, "Job marked as completed successfully. Earnings added to your wallet.", ["earned" => $amount]);
} catch (Exception $e) {
    $conn->rollback();
    sendResponse(false, "Failed to complete job: " . $e->getMessage());
}
?>
