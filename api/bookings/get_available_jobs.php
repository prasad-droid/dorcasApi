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

// Fetch available jobs that match the technician's categories/services
// For now, returning all pending jobs without a vendor assigned
$query = "
    SELECT 
        b.*, 
        c.name as customer_name,
        s.service_name,
        cat.category_name,
        sub.subcategory_img as image
    FROM bookings b
    JOIN customers c ON b.customer_id = c.id
    LEFT JOIN services s ON b.service_id = s.id
    LEFT JOIN subcategories sub ON s.subcategory_id = sub.id
    LEFT JOIN categories cat ON sub.category_id = cat.id
    WHERE (b.vendor_id IS NULL OR b.vendor_id = 0)
    AND b.status = 'pending'
    ORDER BY b.created_at DESC
";

$result = $conn->query($query);

$jobs = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
}

sendResponse(true, "Available jobs fetched successfully", $jobs);
?>