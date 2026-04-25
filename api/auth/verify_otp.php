<?php
header("Content-Type: application/json");

require "../config/db.php";
require "../helpers/response.php";

$phone = $_POST['phone'] ?? '';
$otp   = $_POST['otp'] ?? '';
$role  = $_POST['role'] ?? '';

if (!$phone || !$otp || !$role) {
    sendResponse(false, "Phone, OTP and role required");
}

// validate role
if (!in_array($role, ['customer', 'vendor'])) {
    sendResponse(false, "Invalid role");
}

// decide table
$table = ($role === 'vendor') ? 'vendors' : 'customers';

// fetch user
$result = $conn->query("SELECT * FROM $table WHERE phone='$phone' LIMIT 1");

if ($result->num_rows == 0) {
    sendResponse(false, "User not found");
}

$user = $result->fetch_assoc();

// check OTP
if ($user['otp'] != $otp) {
    sendResponse(false, "Invalid OTP");
}

// check expiry
if (!$user['otp_expires_at'] || strtotime($user['otp_expires_at']) < time()) {
    sendResponse(false, "OTP expired");
}

// mark verified
$conn->query("
    UPDATE $table 
    SET otp=NULL, phone_verified=1 
    WHERE id=".$user['id']
);

// 🔐 Generate token
$token = bin2hex(random_bytes(32));
$expiry = date("Y-m-d H:i:s", strtotime("+7 days"));

// store token based on role
if ($role === 'vendor') {
    $conn->query("
        INSERT INTO vendor_remember_tokens (vendor_id, token, expires_at)
        VALUES (".$user['id'].", '$token', '$expiry')
    ");
} else {
    $conn->query("
        INSERT INTO remember_tokens (customer_id, token, expires_at)
        VALUES (".$user['id'].", '$token', '$expiry')
    ");
}

// prepare response
$response = [
    "user_id" => $user['id'],
    "role" => $role,
    "token" => $token
];

// add extra fields (optional)
if ($role === 'vendor') {
    $response['name'] = $user['name'];
    $response['phone'] = $user['phone'];
    $response['rating'] = $user['rating'];
} else {
    $response['name'] = $user['name'] ?? '';
    $response['phone'] = $user['phone'];
}

sendResponse(true, "Login successful", $response);
?>
