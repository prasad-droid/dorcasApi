<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$vendor_id = isset($_GET['vendor_id']) ? intval($_GET['vendor_id']) : 0;

if ($vendor_id <= 0) {
    sendResponse(false, "Invalid Vendor ID provided");
}

$query = "
    SELECT 
        v.id, 
        v.name, 
        v.rating,
        (SELECT MIN(service_price) FROM services WHERE vendor_id = v.id) as price
    FROM vendors v
    WHERE v.id = ? 
    LIMIT 1
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    sendResponse(true, "Vendor details fetched successfully", $row);
} else {
    sendResponse(false, "Vendor not found");
}
?>
