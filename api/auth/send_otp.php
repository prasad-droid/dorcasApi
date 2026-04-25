<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
require "../config/db.php";
require "../helpers/response.php";
require "../helpers/otp.php";
require "../helpers/sms.php";

$phone = $_POST['phone'] ?? '';
$role = $_POST['role'] ?? '';

if (!$phone) {
    sendResponse(false, "Phone number required");
}

// generate OTP
$otp = generateOTP();
$expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

// check if customer exists
$check = $conn->query("SELECT id FROM customers WHERE phone='$phone'");

if ($check->num_rows > 0) {
    // update OTP
    $conn->query("UPDATE customers SET otp='$otp', otp_expires_at='$expiry' WHERE phone='$phone'");
} else {
    // create new user
    $conn->query("INSERT INTO customers (phone, otp, otp_expires_at) VALUES ('$phone', '$otp', '$expiry')");
}

$sms = sendSMS($phone, $otp);
if (!$sms['status']) {
    sendResponse(false, "Failed to send OTP",$sms);
}else{
    sendResponse(true,"OTP Sent Successfully");
}
?>