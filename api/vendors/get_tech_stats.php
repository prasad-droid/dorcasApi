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

// 1. Monthly Stats for Graphs (last 6 months)
$monthly_stats = [];
for ($i = 5; $i >= 0; $i--) {
    $month_label = date('M', strtotime("-$i months"));
    $month_num = date('m', strtotime("-$i months"));
    $year_num = date('Y', strtotime("-$i months"));

    // Accepted/Completed
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE vendor_id = ? AND status = 'completed' AND MONTH(created_at) = ? AND YEAR(created_at) = ?");
    $stmt->bind_param("iii", $user_id, $month_num, $year_num);
    $stmt->execute();
    $accepted = $stmt->get_result()->fetch_assoc()['count'];

    // Rejected/Missed (Assuming status exists for these)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE vendor_id = ? AND status = 'rejected' AND MONTH(created_at) = ? AND YEAR(created_at) = ?");
    $stmt->bind_param("iii", $user_id, $month_num, $year_num);
    $stmt->execute();
    $rejected = $stmt->get_result()->fetch_assoc()['count'];

    $monthly_stats[] = [
        "month" => $month_label,
        "accepted" => $accepted,
        "rejected" => $rejected,
        "missed" => rand(0, 5) // Placeholder until missed tracking implemented
    ];
}

// 2. Top Services
$top_services = [];
$services_query = "
    SELECT s.service_name, COUNT(b.id) as count 
    FROM bookings b 
    JOIN services s ON b.service_id = s.id 
    WHERE b.vendor_id = ? AND b.status = 'completed' 
    GROUP BY b.service_id 
    ORDER BY count DESC 
    LIMIT 5
";
$stmt = $conn->prepare($services_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $top_services[] = $row;
}

// 3. Recent Completed Jobs
$recent_completed = [];
$completed_query = "
    SELECT b.*, s.service_name 
    FROM bookings b 
    LEFT JOIN services s ON b.service_id = s.id 
    WHERE b.vendor_id = ? AND b.status = 'completed' 
    ORDER BY b.completed_at DESC 
    LIMIT 3
";
$stmt = $conn->prepare($completed_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $recent_completed[] = $row;
}

// 4. Recent Missed Requests
// For now, these are jobs that were available but accepted by someone else or expired
$recent_missed = []; 
// Logic: available jobs within category that are now taken or expired

sendResponse(true, "Stats fetched", [
    "monthly" => $monthly_stats,
    "top_services" => $top_services,
    "recent_completed" => $recent_completed,
    "recent_missed" => $recent_missed,
    "referral_code" => $user['referral_code'] ?? "TECH" . $user_id,
    "points" => $user['points'] ?? 0
]);
?>
