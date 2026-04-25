<?php
header("Content-Type: application/json");

require "../config/db.php";
require "../helpers/response.php";

// get headers
$headers = getallheaders();

$authHeader = $headers['Authorization'] ?? '';
$role = strtolower($headers['Role'] ?? '');

if (!$authHeader || !$role) {
    sendResponse(false, "Missing token or role");
}

// extract token
$token = str_replace("Bearer ", "", $authHeader);

// validate role
if (!in_array($role, ['customer', 'vendor'])) {
    sendResponse(false, "Invalid role");
}

// delete token
if ($role === 'vendor') {
    $conn->query("DELETE FROM vendor_remember_tokens WHERE token='$token'");
} else {
    $conn->query("DELETE FROM remember_tokens WHERE token='$token'");
}

sendResponse(true, "Logged out successfully");
?>
