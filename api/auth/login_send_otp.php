<?php
require "../config/db.php";
require "../helpers/response.php";
require "../helpers/otp.php";

$data = json_decode(file_get_contents("php://input"), true);

$phone = $data['phone'] ?? '';
$role  = $data['role'] ?? '';

if (!$phone || !$role) {
    sendResponse(false, "Phone and role required");
}

if (!in_array($role, ['customer', 'technician'])) {
    sendResponse(false, "Invalid role");
}

$table = ($role === 'technician') ? 'vendors' : 'customers';

// Check if user exists
$stmt = $conn->prepare("SELECT id FROM $table WHERE phone=? LIMIT 1");
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    sendResponse(false, "User not found. Please register first.");
}

// Generate OTP
$otp = generateOTP();
$expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

// Save OTP
$stmt = $conn->prepare("
    UPDATE $table 
    SET otp=?, otp_expires_at=? 
    WHERE phone=?
");
$stmt->bind_param("sss", $otp, $expiry, $phone);
$stmt->execute();

// sendSMS($phone, $otp); // enable in production

sendResponse(true, "OTP sent", [
    "otp" => $otp // remove in production
]);
?>