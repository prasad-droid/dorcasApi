<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

if ($GLOBALS['auth_role'] !== 'customer') {
    sendResponse(false, "Unauthorized");
}

$customer_id = $GLOBALS['auth_user']['id'];
// 🧠 Check if user has at least 3 scratched cards
$card_check = "SELECT COUNT(*) as count FROM scratch_cards WHERE customer_id = ? AND is_scratched = 1";
$c_stmt = $conn->prepare($card_check);
$c_stmt->bind_param("i", $customer_id);
$c_stmt->execute();
$scratched_count = $c_stmt->get_result()->fetch_assoc()['count'];

if ($scratched_count < 3) {
    sendResponse(false, "You need at least 3 scratched cards to request a payout. Current: $scratched_count");
}

$points_to_redeem = isset($_POST['points']) ? intval($_POST['points']) : 0;

if ($points_to_redeem <= 0) {
    sendResponse(false, "No points provided for redemption");
}

// 1. Check current balance
$check_query = "SELECT balance FROM customer_wallet WHERE customer_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$res = $stmt->get_result();
$wallet = $res->fetch_assoc();

if (!$wallet || $wallet['balance'] < $points_to_redeem) {
    sendResponse(false, "Insufficient points balance");
}

$rupees = $points_to_redeem / 10; // 10 points = 1 Rupee

$conn->begin_transaction();

try {
    // Deduct points and add to referral_balance (cash wallet)
    $update_query = "UPDATE customer_wallet SET 
        balance = balance - ?, 
        referral_balance = referral_balance + ?,
        total_redeemed = total_redeemed + ?,
        last_redeemed_at = NOW()
        WHERE customer_id = ?";
    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("idii", $points_to_redeem, $rupees, $points_to_redeem, $customer_id);
    $stmt->execute();

    $conn->commit();
    sendResponse(true, "Redeemed ₹" . number_format($rupees, 2) . " successfully", ["new_points" => $wallet['balance'] - $points_to_redeem, "rupees_added" => $rupees]);
} catch (Exception $e) {
    $conn->rollback();
    sendResponse(false, "Redemption failed: " . $e->getMessage());
}
?>
