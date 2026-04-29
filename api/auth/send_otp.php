<?php
require "../config/db.php";
require "../helpers/response.php";
require "../helpers/otp.php";
// require "../helpers/sms.php"; // enable in production

$name  = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$role  = $_POST['role'] ?? 'customer';

// Validation
if (!$phone) {
    sendResponse(false, "Phone number required");
}

if (!in_array($role, ['customer', 'technician'])) {
    sendResponse(false, "Invalid role");
}

// Generate OTP
$otp = generateOTP();
$expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

// Select table
$table = ($role === 'technician') ? 'vendors' : 'customers';

// Check if user exists
$stmt = $conn->prepare("SELECT id FROM $table WHERE phone=?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update OTP
    $conn->query("
        UPDATE $table 
        SET otp='$otp', otp_expires_at='$expiry' 
        WHERE phone='$phone'
    ");
} else {
    // Insert new user
    if ($role === 'technician') {
        $conn->query("
            INSERT INTO vendors (phone, otp, otp_expires_at, password) 
            VALUES ('$phone', '$otp', '$expiry', '')
        ");
    } else {
        $conn->query("
            INSERT INTO customers (name, phone, otp, otp_expires_at) 
            VALUES ('$name', '$phone', '$otp', '$expiry')
        ");
    }
}

// Send SMS (enable in real app)
// sendSMS($phone, $otp);

sendResponse(true, "OTP Sent Successfully", [
    "otp" => $otp // remove in production
]);
?>