<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];
$user_id = $user['id'];

if ($role !== 'technician') {
    sendResponse(false, "Unauthorized");
}

$query = "
    SELECT s.id, s.service_name, s.service_img as image_path, s.service_price as price, c.category_name 
    FROM vendor_services vs 
    JOIN services s ON vs.service_id = s.id 
    JOIN categories c ON s.category_id = c.id 
    WHERE vs.vendor_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$services = [];

while ($row = $res->fetch_assoc()) {
    $services[] = $row;
}

sendResponse(true, "Vendor services", $services);
?>
