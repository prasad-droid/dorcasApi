<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

// Only customers can book
if ($GLOBALS['auth_role'] !== 'customer') {
    sendResponse(false, "Unauthorized: Only customers can create bookings");
}

$user = $GLOBALS['auth_user'];
$customer_id = $user['id'];

// Get POST data
$vendor_id = isset($_POST['vendor_id']) ? intval($_POST['vendor_id']) : 0;
$service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
$booking_name = $_POST['booking_name'] ?? '';
$booking_phone = $_POST['booking_phone'] ?? '';
$address = $_POST['address'] ?? '';
$city = $_POST['city'] ?? '';
$pincode = $_POST['pincode'] ?? '';
$service_date = $_POST['service_date'] ?? '';
$service_time = $_POST['service_time'] ?? '';
$notes = $_POST['notes'] ?? '';
$payment_mode = $_POST['payment_mode'] ?? 'cash';

if ($vendor_id <= 0 || empty($booking_name) || empty($booking_phone) || empty($service_date)) {
    sendResponse(false, "Missing required booking details");
}

$query = "INSERT INTO bookings (
    customer_id, vendor_id, service_id, booking_name, booking_phone, 
    address, city, pincode, service_date, service_time, notes, 
    status, payment_mode, created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())";

$stmt = $conn->prepare($query);

if (!$stmt) {
    sendResponse(false, "Database error: " . $conn->error);
}

$stmt->bind_param(
    "iiisssssssss", 
    $customer_id, $vendor_id, $service_id, $booking_name, $booking_phone,
    $address, $city, $pincode, $service_date, $service_time, $notes,
    $payment_mode
);

if ($stmt->execute()) {
    $booking_id = $conn->insert_id;

    // Generate a scratch card for the new booking
    $reward_amount = rand(10, 499);
    $scratch_card_query = "INSERT INTO scratch_cards (booking_id, customer_id, reward_amount, is_scratched, created_at) VALUES (?, ?, ?, 0, NOW())";
    $sc_stmt = $conn->prepare($scratch_card_query);
    $sc_stmt->bind_param("iii", $booking_id, $customer_id, $reward_amount);
    $sc_stmt->execute();
    $card_id = $conn->insert_id;

    sendResponse(true, "Booking created successfully", [
        "booking_id" => $booking_id,
        "scratch_card" => [
            "id" => $card_id,
            "reward_amount" => $reward_amount
        ]
    ]);
} else {
    sendResponse(false, "Failed to create booking: " . $stmt->error);
}
?>
