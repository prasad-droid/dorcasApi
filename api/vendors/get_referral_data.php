<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$user = $GLOBALS['auth_user'];
$user_id = $user['id'];

try {
    // 1. Get referral code from vendors table
    $v_stmt = $conn->prepare("SELECT referral_code FROM vendors WHERE id = ?");
    $v_stmt->bind_param("i", $user_id);
    $v_stmt->execute();
    $v_res = $v_stmt->get_result()->fetch_assoc();
    $referral_code = $v_res['referral_code'] ?? "DORCAS" . $user_id;

    // 2. Get wallet data
    $w_stmt = $conn->prepare("SELECT referral_points, referral_balance FROM vendor_wallet WHERE vendor_id = ?");
    $w_stmt->bind_param("i", $user_id);
    $w_stmt->execute();
    $w_res = $w_stmt->get_result()->fetch_assoc();
    
    // If no wallet exists yet, create one or return zeros
    if (!$w_res) {
        $referral_points = 0;
        $referral_balance = 0.00;
        
        // Optional: Create wallet record
        $conn->query("INSERT INTO vendor_wallet (vendor_id, balance, referral_points, referral_balance) VALUES ($user_id, 0, 0, 0)");
    } else {
        $referral_points = $w_res['referral_points'];
        $referral_balance = $w_res['referral_balance'];
    }

    // 3. Get recent referral logs
    $l_stmt = $conn->prepare("SELECT rl.*, v.name as referee_name 
                             FROM vendor_referral_logs rl 
                             JOIN vendors v ON rl.referee_id = v.id 
                             WHERE rl.referrer_id = ? 
                             ORDER BY rl.created_at DESC LIMIT 10");
    $l_stmt->bind_param("i", $user_id);
    $l_stmt->execute();
    $logs_res = $l_stmt->get_result();
    $logs = [];
    while ($row = $logs_res->fetch_assoc()) {
        $logs[] = $row;
    }

    sendResponse(true, "Referral data fetched", [
        "referral_code" => $referral_code,
        "points" => $referral_points,
        "balance" => $referral_balance,
        "history" => $logs
    ]);

} catch (Exception $e) {
    sendResponse(false, "Error: " . $e->getMessage());
}
?>
