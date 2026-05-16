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

$reviews = [];
$reviews_query = "
    SELECT r.*, c.name as customer_name 
    FROM reviews r 
    JOIN customers c ON r.customer_id = c.id 
    WHERE r.vendor_id = ? 
    ORDER BY r.created_at DESC
";
$stmt = $conn->prepare($reviews_query);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $reviews_result = $stmt->get_result();
    if ($reviews_result) {
        while ($rev = $reviews_result->fetch_assoc()) {
            $reviews[] = [
                "name" => $rev['customer_name'] ?? "Anonymous",
                "rating" => (float)($rev['rating'] ?? 5),
                "comment" => $rev['review_text'] ?? "",
                "date" => isset($rev['created_at']) ? date("d M Y", strtotime($rev['created_at'])) : "Recent"
            ];
        }
    }
}

// Get stats
$stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE vendor_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

sendResponse(true, "Reviews fetched", [
    "reviews" => $reviews,
    "stats" => [
        "avg_rating" => number_format((float)($stats['avg_rating'] ?? 0), 1),
        "total_reviews" => (int)($stats['count'] ?? 0)
    ]
]);
?>
