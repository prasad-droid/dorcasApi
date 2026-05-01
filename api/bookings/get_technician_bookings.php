<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];
$user_id = $user['id'];

if ($role !== 'technician') {
    sendResponse(false, "Unauthorized: Technician access only");
}

$status = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT b.*, c.name as customer_name, c.phone as customer_phone,s.service_name,cat.subcategory_name FROM bookings b JOIN customers c ON b.customer_id = c.id LEFT JOIN services s ON b.service_id = s.id LEFT JOIN subcategories cat ON s.subcategory_id = cat.id WHERE b.vendor_id = ? AND b.status = '$status' ORDER BY b.created_at DESC";


$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

sendResponse(true, "Bookings fetched successfully", $bookings);
?>