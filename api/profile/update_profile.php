<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];
$user_id = $user['id'];

$name = trim($data['name'] ?? $user['name']);
$email = trim($data['email'] ?? $user['email'] ?? '');
$address = trim($data['address'] ?? $user['address'] ?? '');
$city = trim($data['city'] ?? $user['city'] ?? '');
$state = trim($data['state'] ?? $user['state'] ?? '');
$pincode = trim($data['pincode'] ?? $user['pincode'] ?? '');

if (!$name) {
    sendResponse(false, "Name cannot be empty");
}

if ($role === 'technician') {
    $stmt = $conn->prepare("UPDATE vendors SET name=?, address=?, city=?, state=?, pincode=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $address, $city, $state, $pincode, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE customers SET name=?, email=?, address=?, city=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $email, $address, $city, $user_id);
}

if ($stmt->execute()) {
    sendResponse(true, "Profile updated successfully");
} else {
    sendResponse(false, "Failed to update profile: " . $conn->error);
}
?>
