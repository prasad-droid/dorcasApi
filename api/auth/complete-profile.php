<?php
header("Content-Type: application/json");
require "../middleware/auth.php";

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';
$city = $_POST['city'] ?? '';
$state = $_POST['state'] ?? '';
$pincode = $_POST['pincode'] ?? '';
$services = $_POST['services'] ?? ''; // For vendors

if ($role === 'vendor') {
    $conn->query("
        UPDATE vendors 
        SET name='$name', email='$email', address='$address', city='$city', state='$state', pincode='$pincode', services='$services'
        WHERE id=".$user['id']
    );
} else {
    $conn->query("
        UPDATE customers 
        SET name='$name', email='$email', address='$address', city='$city', state='$state', pincode='$pincode'
        WHERE id=".$user['id']
    );
}

sendResponse(true, "Profile updated successfully");
?>
