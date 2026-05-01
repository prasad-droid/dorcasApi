<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];
$user_id = $user['id'];

if ($role !== 'technician') {
    sendResponse(false, "Unauthorized: Technician access only");
}

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);
$job_id = isset($input['job_id']) ? intval($input['job_id']) : 0;

if ($job_id <= 0) {
    sendResponse(false, "Invalid Job ID");
}

// 1. Check if technician is approved
if (!$user['is_approved'] && ($user['kyc_status'] !== 'approved')) {
    sendResponse(false, "Your account is not yet verified. Please complete KYC.");
}

// 2. Check if job is still available
$check_query = "SELECT vendor_id, status FROM bookings WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res) {
    sendResponse(false, "Job not found");
}

if ($res['vendor_id'] != 0 || $res['status'] !== 'pending') {
    sendResponse(false, "This job is no longer available");
}

// 3. Accept job
$update_query = "UPDATE bookings SET vendor_id = ?, status = 'ongoing' WHERE id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("ii", $user_id, $job_id);

if ($stmt->execute()) {
    sendResponse(true, "Job accepted successfully! You can now view it in your ongoing tasks.");
} else {
    sendResponse(false, "Failed to accept job. Please try again.");
}
?>
