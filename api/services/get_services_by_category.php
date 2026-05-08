<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

if ($category_id <= 0) {
    sendResponse(false, "Category ID is required");
}

$services = [];
$stmt = $conn->prepare("
    SELECT id, service_name, service_price, service_img, service_desc 
    FROM services 
    WHERE category_id=? AND status=1 AND deleted_at IS NULL
");

$stmt->bind_param("i", $category_id);
$stmt->execute();
$res = $stmt->get_result();

while ($srv = $res->fetch_assoc()) {
    $services[] = $srv;
}

sendResponse(true, "Services fetched successfully", $services);
?>
