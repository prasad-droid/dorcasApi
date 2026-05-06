<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/fcm.php";

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];
$user_id = $user['id'];

if ($role !== 'technician') {
    sendResponse(false, "Unauthorized: Technician access only");
}

$input = json_decode(file_get_contents("php://input"), true);
$job_id = isset($input['job_id']) ? intval($input['job_id']) : 0;

if ($job_id <= 0) {
    sendResponse(false, "Invalid Job ID");
}

// 1. Fetch booking to verify
$check_query = "SELECT b.customer_id, s.service_name 
                FROM bookings b 
                LEFT JOIN services s ON b.service_id = s.id 
                WHERE b.id = ? LIMIT 1";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    sendResponse(false, "Job not found");
}

// 2. Logic: If it was a direct booking, set status back to 'pending' with vendor_id = 0
// Or mark it as rejected by this vendor in a log
// For simplicity, we'll just set it back to pending so other technicians can see it
$update_query = "UPDATE bookings SET vendor_id = 0, status = 'pending' WHERE id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("i", $job_id);

if ($stmt->execute()) {
    // 3. Notify Customer
    $msg = "Technician " . $user['name'] . " is unavailable for your " . ($booking['service_name'] ?? "service") . ". We are looking for another technician.";
    createNotification($conn, $booking['customer_id'], 'customer', 'booking_rejected', 'Technician Unavailable', $msg, $job_id);

    sendResponse(true, "Job rejected. We'll notify the customer and look for other vendors.");
} else {
    sendResponse(false, "Failed to reject job");
}
?>
