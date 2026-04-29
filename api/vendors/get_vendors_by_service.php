<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

// Get subcategory_id from query parameter (passed as service_id from frontend)
$subcategory_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;

if ($subcategory_id <= 0) {
    sendResponse(false, "Invalid ID provided");
}

$vendors = [];

/**
 * Query to fetch vendors for a specific subcategory
 * 1. Join vendors with vendor_services to see who offers what.
 * 2. Join with services to get specific pricing and images.
 * 3. Group by vendor to avoid duplicates if they offer multiple services in one subcategory.
 */
$query = "
    SELECT 
        v.id, 
        v.name, 
        v.phone, 
        v.address, 
        v.city, 
        v.rating, 
        v.total_jobs,
        v.kyc_status,
        MIN(s.service_price) as min_price,
        MAX(s.service_price) as max_price,
        COALESCE(s.service_img, sub.subcategory_img) as display_image,
        (SELECT COUNT(*) FROM reviews r WHERE r.vendor_id = v.id) as actual_review_count
    FROM vendors v
    JOIN vendor_services vs ON v.id = vs.vendor_id
    JOIN services s ON vs.service_id = s.id
    LEFT JOIN subcategories sub ON s.subcategory_id = sub.id
    WHERE s.subcategory_id = ? 
    AND v.status = 1 
    AND v.deleted_at IS NULL
    GROUP BY v.id
";

$stmt = $conn->prepare($query);

if (!$stmt) {
    sendResponse(false, "Database error: " . $conn->error);
}

$stmt->bind_param("i", $subcategory_id);
$stmt->execute();
$result = $stmt->get_result();
// echo json_encode($result);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $vendors[] = [
            "id" => $row['id'],
            "name" => $row['name'],
            "phone" => $row['phone'],
            "address" => $row['address'],
            "city" => $row['city'],
            "rating" => $row['rating'],
            "total_jobs" => $row['total_jobs'],
            "review_count" => $row['actual_review_count'],
            "price" => $row['min_price'], // Showing starting price
            "image" => $row['display_image'],
            "kyc_status" => $row['kyc_status']
        ];
    }
    sendResponse(true, "Service providers fetched successfully", $vendors);
} else {
    sendResponse(false, "No service providers found");
}
?>
