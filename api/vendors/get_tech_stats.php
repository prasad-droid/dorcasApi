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

// 2. Activity Rates & Totals
// Count from vendor_booking_requests for deep tracking
$stmt = $conn->prepare("SELECT COUNT(*) n FROM vendor_booking_requests WHERE vendor_id = ? AND status = 'accepted'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_vbr_accepted = (int) $stmt->get_result()->fetch_assoc()['n'];

$stmt = $conn->prepare("SELECT COUNT(*) n FROM vendor_booking_requests WHERE vendor_id = ? AND status = 'declined'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_vbr_declined = (int) $stmt->get_result()->fetch_assoc()['n'];

$stmt = $conn->prepare("SELECT COUNT(*) n FROM vendor_booking_requests WHERE vendor_id = ? AND status = 'expired'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_vbr_expired = (int) $stmt->get_result()->fetch_assoc()['n'];

// Count from bookings table (the source of truth for assignments)
$stmt = $conn->prepare("SELECT COUNT(*) n FROM bookings WHERE vendor_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_bookings_assigned = (int) $stmt->get_result()->fetch_assoc()['n'];

// Use the larger of the two for accepted count (in case vbr is not used for some jobs)
$total_accepted = max($total_vbr_accepted, $total_bookings_assigned);

// Total requests seen by technician
$total_requests = $total_accepted + $total_vbr_declined + $total_vbr_expired;

$stmt = $conn->prepare("SELECT COUNT(*) n FROM bookings WHERE vendor_id = ? AND status = 'completed'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_completed = (int) $stmt->get_result()->fetch_assoc()['n'];

// Calculate Commission Dues
$comm_query = "
    SELECT SUM(COALESCE(b.commission_amount, 0)) as total_due
    FROM bookings b
    WHERE b.vendor_id = ? AND b.status = 'completed' AND b.commission_status = 'pending'
";
$stmt = $conn->prepare($comm_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_commission_due = (float) ($stmt->get_result()->fetch_assoc()['total_due'] ?? 0);

$acceptance_rate = $total_requests > 0 ? round(($total_accepted / $total_requests) * 100) : 0;
$completion_rate = $total_accepted > 0 ? round(($total_completed / $total_accepted) * 100) : 0;

// 3. Top Services
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

// 4. Recent Completed Jobs
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

// 5. Recent Missed Requests
$recent_missed = [];
$missed_query = "
    SELECT vbr.*, s.service_name, b.service_date
    FROM vendor_booking_requests vbr
    JOIN bookings b ON b.id = vbr.booking_id
    JOIN services s ON s.id = b.service_id
    WHERE vbr.vendor_id = ? AND vbr.status IN ('declined', 'expired')
    ORDER BY vbr.sent_at DESC
    LIMIT 3
";
$stmt = $conn->prepare($missed_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $recent_missed[] = $row;
}

// Prepare final activity metrics
$activity_metrics = [
    "total_requests" => (int) $total_requests,
    "total_accepted" => (int) $total_accepted,
    "total_declined" => (int) ($total_vbr_declined ?? 0),
    "total_expired" => (int) ($total_vbr_expired ?? 0),
    "total_completed" => (int) $total_completed,
    "total_commission_due" => (float) $total_commission_due,
    "acceptance_rate" => $acceptance_rate . "%",
    "completion_rate" => $completion_rate . "%"
];

sendResponse(true, "Stats fetched", [
    "monthly" => $monthly_stats,
    "activity" => $activity_metrics,
    "top_services" => $top_services,
    "recent_completed" => $recent_completed,
    "recent_missed" => $recent_missed,
    "referral_code" => $user['referral_code'] ?? "TECH" . $user_id,
    "points" => $user['points'] ?? 0
]);
?>