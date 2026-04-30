<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

if ($GLOBALS['auth_role'] !== 'customer') {
    sendResponse(false, "Unauthorized");
}

$customer_id = $GLOBALS['auth_user']['id'];

$query = "
    SELECT 
        b.id, 
        b.service_date as date, 
        b.service_time as time, 
        b.status, 
        b.payment_mode,
        b.amount_paid,
        sub.subcategory_name as service,
        v.name as provider,
        (SELECT MIN(service_price) FROM services WHERE vendor_id = b.vendor_id AND subcategory_id = b.service_id) as price
    FROM bookings b
    JOIN subcategories sub ON b.service_id = sub.id
    JOIN vendors v ON b.vendor_id = v.id
    WHERE b.customer_id = ?
    ORDER BY b.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    // Format status for frontend (capitalize first letter)
    $row['status'] = ucfirst($row['status']);
    
    // Format price
    $row['price'] = "₹" . ($row['amount_paid'] > 0 ? $row['amount_paid'] : ($row['price'] ?? "0"));
    
    $bookings[] = $row;
}

sendResponse(true, "Bookings fetched successfully", $bookings);
?>
