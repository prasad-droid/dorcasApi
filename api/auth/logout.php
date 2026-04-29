<?php
require "../config/db.php";
require "../helpers/response.php";

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
$role = strtolower($headers['Role'] ?? $headers['role'] ?? '');

if ($authHeader && $role) {
    $token = str_replace("Bearer ", "", $authHeader);
    
    if ($role === 'vendor') {
        $conn->query("DELETE FROM vendor_remember_tokens WHERE token='$token'");
    } else {
        $conn->query("DELETE FROM remember_tokens WHERE token='$token'");
    }
}

sendResponse(true, "Logged out successfully");
?>
