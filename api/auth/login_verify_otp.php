<?php
require "../config/db.php";
require "../helpers/response.php";

$data = json_decode(file_get_contents("php://input"), true);

$phone = $data['phone'] ?? '';
$otp   = $data['otp'] ?? '';
$role  = $data['role'] ?? '';

if (!$phone || !$otp || !$role) {
    sendResponse(false, "Phone, OTP and role required");
}

if (!in_array($role, ['customer', 'technician'])) {
    sendResponse(false, "Invalid role");
}

$table = ($role === 'technician') ? 'vendors' : 'customers';

// Get user
$stmt = $conn->prepare("SELECT * FROM $table WHERE phone=? LIMIT 1");
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();

if (!$user) {
    sendResponse(false, "User not found");
}

// Validate OTP
if ($user['otp'] != $otp) {
    sendResponse(false, "Invalid OTP");
}

// Check expiry
if (!$user['otp_expires_at'] || strtotime($user['otp_expires_at']) < time()) {
    sendResponse(false, "OTP expired");
}

// Clear OTP
$conn->query("UPDATE $table SET otp=NULL WHERE id=".$user['id']);

// Generate token
$token  = bin2hex(random_bytes(32));
$expiry = date("Y-m-d H:i:s", strtotime("+7 days"));

// Store token
if ($role === 'technician') {
    $conn->query("
        INSERT INTO vendor_remember_tokens (vendor_id, token, expires_at)
        VALUES ({$user['id']}, '$token', '$expiry')
    ");
} else {
    $conn->query("
        INSERT INTO remember_tokens (customer_id, token, expires_at)
        VALUES ({$user['id']}, '$token', '$expiry')
    ");
}

// Response
sendResponse(true, "Login successful", [
    "user_id" => $user['id'],
    "role"    => $role,
    "token"   => $token,
    "name"    => $user['name'] ?? ''
]);
?>