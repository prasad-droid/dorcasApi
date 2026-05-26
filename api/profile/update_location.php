<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];
$user_id = $user['id'];

$lat = isset($data['latitude']) ? (float)$data['latitude'] : null;
$lng = isset($data['longitude']) ? (float)$data['longitude'] : null;

if ($lat === null || $lng === null) {
    sendResponse(false, "Latitude and longitude are required");
}

if ($role === 'technician') {
    $stmt = $conn->prepare("UPDATE vendors SET latitude=?, longitude=? WHERE id=?");
    $stmt->bind_param("ddi", $lat, $lng, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE customers SET latitude=?, longitude=? WHERE id=?");
    $stmt->bind_param("ddi", $lat, $lng, $user_id);
}

if ($stmt->execute()) {
    sendResponse(true, "Location updated successfully");
} else {
    sendResponse(false, "Failed to update location: " . $conn->error);
}
?>
