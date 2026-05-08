<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];
$user_id = $user['id'];

// Map the user_type from role header
$user_type = ($role === 'technician') ? 'vendor' : 'customer';

// 🧹 Auto-cleanup: Delete notifications older than 24 hours
$conn->query("DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");

// Fetch notifications for this user
$query = "SELECT id, type, title, body as message, created_at 
          FROM notifications 
          WHERE user_id = ? AND user_type = ? 
          ORDER BY created_at DESC 
          LIMIT 50";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $user_id, $user_type);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    // Add time_ago logic
    $created_at = strtotime($row['created_at']);
    $diff = time() - $created_at;
    
    if ($diff < 60) {
        $row['time_ago'] = "Just now";
    } elseif ($diff < 3600) {
        $row['time_ago'] = floor($diff / 60) . "m ago";
    } elseif ($diff < 86400) {
        $row['time_ago'] = floor($diff / 3600) . "h ago";
    } else {
        $row['time_ago'] = floor($diff / 86400) . "d ago";
    }
    
    // Normalize type for frontend icons
    // Frontend expects: 'booking', 'offer', 'alert'
    if (strpos($row['type'], 'booking') !== false) {
        $row['type'] = 'booking';
    } elseif (strpos($row['type'], 'offer') !== false || strpos($row['type'], 'referral') !== false) {
        $row['type'] = 'offer';
    } else {
        $row['type'] = 'alert';
    }

    $notifications[] = $row;
}

sendResponse(true, "Notifications fetched successfully", $notifications);
?>
