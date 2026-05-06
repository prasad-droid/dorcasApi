<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];
$user_id = $user['id'];

// 🔒 Only technician allowed
if ($role !== 'technician') {
    sendResponse(false, "Unauthorized: Technician access only");
}

// 📥 Get status from URL
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// 🧠 Base query
$query = "SELECT 
            b.*, 
            c.name AS customer_name, 
            c.phone AS customer_phone,
            s.service_name,
            s.service_price,
            cat.category_name,
            sub.subcategory_name,
            sub.subcategory_img AS image
          FROM bookings b
          JOIN customers c ON b.customer_id = c.id
          LEFT JOIN services s ON b.service_id = s.id
          LEFT JOIN subcategories sub ON s.subcategory_id = sub.id
          LEFT JOIN categories cat ON sub.category_id = cat.id
          WHERE b.vendor_id = ?";

// 🎯 Add status filter only if provided
if (!empty($status)) {
    $query .= " AND b.status = ?";
}

$query .= " ORDER BY b.created_at DESC";

// 🔧 Prepare statement
$stmt = $conn->prepare($query);

if (!$stmt) {
    sendResponse(false, "Query preparation failed");
}

// 🔗 Bind params dynamically
if (!empty($status)) {
    // i = integer, s = string
    $stmt->bind_param("is", $user_id, $status);
} else {
    $stmt->bind_param("i", $user_id);
}

// 🚀 Execute
$stmt->execute();
$result = $stmt->get_result();

// 📦 Fetch data
$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

// 📤 Response
if (count($bookings) > 0) {
    sendResponse(true, "Bookings fetched successfully", $bookings);
} else {
    sendResponse(false, "No bookings found", []);
}
?>