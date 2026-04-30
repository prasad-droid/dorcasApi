<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];
$user_id = $user['id'];

$profile_data = [
    "id" => $user['id'],
    "name" => $user['name'],
    "phone" => $user['phone'],
    "email" => $user['email'] ?? null,
    "address" => $user['address'] ?? null,
    "city" => $user['city'] ?? null,
    "state" => $user['state'] ?? null,
    "pincode" => $user['pincode'] ?? null,
    "referral_code" => $user['referral_code'] ?? null,
    "stats" => []
];

if ($role === 'technician') {
    // Fetch stats for technician
    $completed_jobs_query = "SELECT COUNT(*) as count FROM bookings WHERE vendor_id = ? AND status = 'completed'";
    $stmt = $conn->prepare($completed_jobs_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $completed_jobs = $stmt->get_result()->fetch_assoc()['count'];

    $earnings_query = "SELECT balance FROM vendor_wallet WHERE vendor_id = ?";
    $stmt = $conn->prepare($earnings_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $wallet = $stmt->get_result()->fetch_assoc();
    $earnings = $wallet ? $wallet['balance'] : 0;

    $profile_data['stats'] = [
        "label1" => "Completed",
        "value1" => $completed_jobs,
        "label2" => "Earnings",
        "value2" => "₹" . number_format($earnings, 0),
        "label3" => "Status",
        "value3" => "PARTNER"
    ];
} else {
    // Fetch stats for customer
    $bookings_query = "SELECT COUNT(*) as count FROM bookings WHERE customer_id = ?";
    $stmt = $conn->prepare($bookings_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $bookings_count = $stmt->get_result()->fetch_assoc()['count'];

    $points_query = "SELECT balance, referral_balance FROM customer_wallet WHERE customer_id = ?";
    $stmt = $conn->prepare($points_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $wallet = $stmt->get_result()->fetch_assoc();
    $points = $wallet ? $wallet['balance'] : 0;
    $wallet_balance = $wallet ? ($wallet['referral_balance'] ?? 0) : 0;

    $profile_data['stats'] = [
        "label1" => "Bookings",
        "value1" => $bookings_count,
        "label2" => "Points",
        "value2" => $points,
        "label3" => "Wallet",
        "value3" => "₹" . number_format($wallet_balance, 0),
        "label4" => "Rank",
        "value4" => $bookings_count > 10 ? "VIP" : "PRO"
    ];
}

sendResponse(true, "Profile fetched successfully", $profile_data);
?>
