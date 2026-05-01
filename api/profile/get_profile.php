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
    try {
        // Fetch stats for technician
        $completed_jobs_query = "SELECT COUNT(*) as count FROM bookings WHERE vendor_id = ? AND status = 'completed'";
        $stmt = $conn->prepare($completed_jobs_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $completed_jobs = $res ? $res->fetch_assoc()['count'] : 0;

        $earnings_query = "SELECT balance, pending_payout FROM vendor_wallet WHERE vendor_id = ?";
        $stmt = $conn->prepare($earnings_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $wallet = $res ? $res->fetch_assoc() : null;
        $earnings = $wallet ? $wallet['balance'] : 0;
        $pending_payout = $wallet ? ($wallet['pending_payout'] ?? 0) : 0;

        // Fetch weekly earnings
        $weekly_query = "SELECT SUM(amount) as total FROM bookings WHERE vendor_id = ? AND status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $stmt = $conn->prepare($weekly_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $weekly_earnings = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

        // Fetch monthly earnings
        $monthly_query = "SELECT SUM(amount) as total FROM bookings WHERE vendor_id = ? AND status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $conn->prepare($monthly_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $monthly_earnings = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

        // Fetch today earnings
        $today_query = "SELECT SUM(amount) as total FROM bookings WHERE vendor_id = ? AND status = 'completed' AND DATE(created_at) = CURDATE()";
        $stmt = $conn->prepare($today_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $today_earnings = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

        // Fetch latest 3 reviews
        $reviews = [];
        $reviews_query = "
            SELECT r.*, c.name as customer_name 
            FROM reviews r 
            JOIN customers c ON r.customer_id = c.id 
            WHERE r.vendor_id = ? 
            ORDER BY r.created_at DESC 
            LIMIT 3
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
                        "rating" => $rev['rating'] ?? 5,
                        "comment" => $rev['comment'] ?? "",
                        "date" => isset($rev['created_at']) ? date("d M Y", strtotime($rev['created_at'])) : "Recent"
                    ];
                }
            }
        }

        // Fetch review count
        $reviews_count_query = "SELECT COUNT(*) as count FROM reviews WHERE vendor_id = ?";
        $stmt = $conn->prepare($reviews_count_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $reviews_count = $stmt->get_result()->fetch_assoc()['count'];

        // Fetch services offered count
        $services_count_query = "SELECT COUNT(*) as count FROM vendor_services WHERE vendor_id = ?";
        $stmt = $conn->prepare($services_count_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $services_count = $stmt->get_result()->fetch_assoc()['count'];

        $profile_data['stats'] = [
            "total_jobs" => $completed_jobs,
            "total_earnings" => $earnings,
            "pending_payout" => $pending_payout,
            "rating" => $user['rating'] ?? "0.0",
            "completed_jobs" => $completed_jobs,
            "active_jobs" => 0,
            "today_earnings" => $today_earnings,
            "weekly_earnings" => $weekly_earnings,
            "monthly_earnings" => $monthly_earnings,
            "acceptance_rate" => "98%",
            "growth" => "+15%",
            "reviews_count" => $reviews_count,
            "services_count" => $services_count,
            "missed_jobs" => 0 // Placeholder
        ];
        $profile_data['reviews'] = $reviews;
        $profile_data['is_approved'] = (bool)($user['is_approved'] ?? ($user['kyc_status'] == 'approved' ? 1 : 0) ?? 0);
    } catch (Exception $e) {
        // Log error but don't break JSON
        $profile_data['error_log'] = $e->getMessage();
    }
} else {
    // Fetch stats for customer
    $bookings_query = "SELECT COUNT(*) as count FROM bookings WHERE customer_id = ?";
    $stmt = $conn->prepare($bookings_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $bookings_count = $res ? $res->fetch_assoc()['count'] : 0;

    $points_query = "SELECT balance, referral_balance FROM customer_wallet WHERE customer_id = ?";
    $stmt = $conn->prepare($points_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $wallet = $res ? $res->fetch_assoc() : null;
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
