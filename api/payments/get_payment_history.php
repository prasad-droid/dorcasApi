<?php
require_once '../config/db.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];

if (!$user || $role !== 'technician') {
    echo json_encode(["status" => false, "message" => "Unauthorized"]);
    exit;
}

$vendor_id = $user['id'];

// Fetch successful CCAvenue orders for this vendor
$query = "
    SELECT * FROM ccav_orders 
    WHERE vendor_id = ? AND status = 'Success' 
    ORDER BY created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}

echo json_encode([
    "status" => true,
    "data" => $history
]);
?>
