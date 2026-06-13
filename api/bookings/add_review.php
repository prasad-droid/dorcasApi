<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/fcm.php";

if ($GLOBALS['auth_role'] !== 'customer') {
    sendResponse(false, "Unauthorized: Only customers can add reviews");
}

$user = $GLOBALS['auth_user'];
$customer_id = $user['id'];

// Get POST data (parsed from JSON in db.php)
$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 5;
$review_text = $_POST['review'] ?? '';

if ($booking_id <= 0 || $rating < 1 || $rating > 5) {
    sendResponse(false, "Invalid review data");
}

// 1. Verify booking exists and belongs to this customer
$booking_query = "SELECT vendor_id, service_id, status FROM bookings WHERE id = ? AND customer_id = ? LIMIT 1";
$stmt = $conn->prepare($booking_query);
$stmt->bind_param("ii", $booking_id, $customer_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    sendResponse(false, "Booking not found or unauthorized");
}

if ($booking['status'] !== 'completed') {
    sendResponse(false, "You can only review completed services");
}

// 🧠 Check if a review already exists for this booking
$exists_query = "SELECT id FROM reviews WHERE booking_id = ? LIMIT 1";
$e_stmt = $conn->prepare($exists_query);
$e_stmt->bind_param("i", $booking_id);
$e_stmt->execute();
if ($e_stmt->get_result()->num_rows > 0) {
    sendResponse(false, "You have already reviewed this service");
}

$vendor_id = $booking['vendor_id'];
$service_id = $booking['service_id'];

$conn->begin_transaction();

try {
    // 2. Insert into reviews
    $insert_query = "INSERT INTO reviews (booking_id, customer_id, vendor_id, service_id, rating, review_text, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $i_stmt = $conn->prepare($insert_query);
    $i_stmt->bind_param("iiiiis", $booking_id, $customer_id, $vendor_id, $service_id, $rating, $review_text);
    $i_stmt->execute();

    // 3. Update Vendor Rating
    // Fetch all ratings for this vendor
    $rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE vendor_id = ?";
    $r_stmt = $conn->prepare($rating_query);
    $r_stmt->bind_param("i", $vendor_id);
    $r_stmt->execute();
    $rating_stats = $r_stmt->get_result()->fetch_assoc();
    
    $new_avg = $rating_stats['avg_rating'] ?? 5.0;
    $new_count = $rating_stats['total_reviews'] ?? 0;

    $update_vendor = "UPDATE vendors SET rating = ? WHERE id = ?";
    $uv_stmt = $conn->prepare($update_vendor);
    $uv_stmt->bind_param("di", $new_avg, $vendor_id);
    $uv_stmt->execute();

    $conn->commit();
    
    // 4. Send Push Notification to Vendor
    $msg = "You received a {$rating}-star review for a completed service!";
    createNotification($conn, $vendor_id, 'vendor', 'new_review', 'New Review Received', $msg, $booking_id, '/tech/dashboard');

    sendResponse(true, "Review submitted successfully");

} catch (Exception $e) {
    $conn->rollback();
    sendResponse(false, "Failed to submit review: " . $e->getMessage());
}
?>
