<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];
$user_id = $user['id'];

$data = json_decode(file_get_contents("php://input"), true);
$device_token = $data['device_token'] ?? null;

if (!$device_token) {
    sendResponse(false, "Device token is required");
}

// Map the table based on role
$table = ($role === 'technician') ? 'vendors' : 'customers';

// Update the device token for the user
$query = "UPDATE $table SET device_token = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $device_token, $user_id);

if ($stmt->execute()) {
    sendResponse(true, "Device token saved successfully");
} else {
    sendResponse(false, "Failed to save device token");
}
?>
