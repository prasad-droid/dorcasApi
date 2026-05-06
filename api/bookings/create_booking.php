<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/fcm.php";

// Only customers can book
if ($GLOBALS['auth_role'] !== 'customer') {
    sendResponse(false, "Unauthorized: Only customers can create bookings");
}

$user = $GLOBALS['auth_user'];
$customer_id = $user['id'];

// Get JSON data if exists
$json_data = json_decode(file_get_contents("php://input"), true);
if ($json_data) {
    $_POST = array_merge($_POST, $json_data);
}

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
$latitude = $_POST['latitude'] ?? '';
$longitude = $_POST['longitude'] ?? '';



if (empty($booking_name) || empty($booking_phone) || empty($service_date) || $service_id <= 0) {
    sendResponse(false, "Missing required booking details (Name, Phone, Date, or Service)");
}

$query = "INSERT INTO bookings (
    customer_id, vendor_id, service_id, booking_name, booking_phone, 
    address, city, pincode, service_date, service_time, notes, 
    status, payment_mode, latitude, longitude, created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, NOW())";

$stmt = $conn->prepare($query);

if (!$stmt) {
    sendResponse(false, "Database error: " . $conn->error);
}

$stmt->bind_param(
    "iiisssssssssdd",
    $customer_id,
    $vendor_id,
    $service_id,
    $booking_name,
    $booking_phone,
    $address,
    $city,
    $pincode,
    $service_date,
    $service_time,
    $notes,
    $payment_mode,
    $latitude,
    $longitude
);

if ($stmt->execute()) {
    $booking_id = $conn->insert_id;

    // 1. Identify and Notify Nearest Technicians (if no vendor specifically chosen)
    if ($vendor_id === 0 && !empty($latitude) && !empty($longitude)) {
        $tech_query = "
            SELECT v.id, 
            (6371 * acos(cos(radians(?)) * cos(radians(v.latitude)) * cos(radians(v.longitude) - radians(?)) + sin(radians(?)) * sin(radians(v.latitude)))) AS distance 
            FROM vendors v
            JOIN vendor_services vs ON v.id = vs.vendor_id
            WHERE vs.service_id = ?
            HAVING distance <= 10
            ORDER BY distance ASC
            LIMIT 5
        ";
        $tech_stmt = $conn->prepare($tech_query);
        $tech_stmt->bind_param("dddi", $latitude, $longitude, $latitude, $service_id);
        $tech_stmt->execute();
        $tech_res = $tech_stmt->get_result();

        while ($tech = $tech_res->fetch_assoc()) {
            $msg = "A new " . ($json_data['service_name'] ?? 'service') . " request is available near you.";
            createNotification($conn, $tech['id'], 'vendor', 'booking_request', 'New Job Request', $msg, $booking_id);
        }
    } else if ($vendor_id > 0) {
        // Notify specific vendor if chosen
        $msg = "You have a new direct booking request from " . $booking_name;
        createNotification($conn, $vendor_id, 'vendor', 'booking_direct', 'New Direct Booking', $msg, $booking_id);
    }

    // 2. Generate a scratch card for the new booking
    $reward_amount = rand(10, 100);
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