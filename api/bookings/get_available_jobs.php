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

$lat = $user['latitude'] ?? 0;
$lng = $user['longitude'] ?? 0;

// Temporary Migration: Add 'ongoing' to status enum if not present and fix broken statuses
$conn->query("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending','confirmed','ongoing','completed','cancelled','no_vendor') DEFAULT 'pending'");
$conn->query("UPDATE bookings SET status = 'ongoing' WHERE (status = '' OR status IS NULL) AND vendor_id > 0 AND vendor_id IS NOT NULL");

// Fetch available jobs that match the technician's services
// Implements a "Ripple Effect": Technicians see jobs after (Distance in KM) minutes
// This ensures the nearest technician has a head start (sequential-like behavior)
$query = "
    SELECT 
        b.*, 
        c.name as customer_name,
        s.service_name,
        s.service_price,
        cat.category_name,
        s.service_img as image,
        (6371 * acos(cos(radians(?)) * cos(radians(b.latitude)) * cos(radians(b.longitude) - radians(?)) + sin(radians(?)) * sin(radians(b.latitude)))) AS distance,
        TIMESTAMPDIFF(MINUTE, b.created_at, NOW()) as minutes_old
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        LEFT JOIN services s ON b.service_id = s.id
        LEFT JOIN subcategories sub ON s.subcategory_id = sub.id
        LEFT JOIN categories cat ON sub.category_id = cat.id
        JOIN vendor_services vs ON (vs.service_id = b.service_id AND vs.vendor_id = ?)
        WHERE (b.vendor_id IS NULL OR b.vendor_id = 0)
        AND b.status = 'pending'
        AND (
            (? = 0 AND ? = 0) OR
            b.latitude IS NULL OR 
            (6371 * acos(cos(radians(?)) * cos(radians(b.latitude)) * cos(radians(b.longitude) - radians(?)) + sin(radians(?)) * sin(radians(b.latitude)))) <= 50
        )
        HAVING (distance IS NULL OR minutes_old >= (distance * 0.5))
        ORDER BY b.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("dddiddddd", $lat, $lng, $lat, $user_id, $lat, $lng, $lat, $lng, $lat);
$stmt->execute();
$result = $stmt->get_result();

$jobs = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
}

sendResponse(true, "Available jobs fetched successfully", $jobs);
?>