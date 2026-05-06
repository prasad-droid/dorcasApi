<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];
$job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($job_id <= 0) {
    sendResponse(false, "Invalid Job ID");
}

$query = "
    SELECT 
    b.*, 
    c.name as customer_name,
    c.phone as customer_phone,
    s.service_name,
    s.service_price,   
    cat.category_name,
    sub.subcategory_img as image
    FROM bookings b
    JOIN customers c ON b.customer_id = c.id
    LEFT JOIN services s ON b.service_id = s.id
    LEFT JOIN subcategories sub ON s.subcategory_id = sub.id
    LEFT JOIN categories cat ON sub.category_id = cat.id
    WHERE b.id = ? 
    LIMIT 1;
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();

if ($job = $result->fetch_assoc()) {
    sendResponse(true, "Job details fetched successfully", $job);
} else {
    sendResponse(false, "Job not found");
}
?>
