<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

if ($GLOBALS['auth_role'] !== 'customer') {
    sendResponse(false, "Unauthorized");
}

$customer_id = $GLOBALS['auth_user']['id'];
$card_id = isset($_POST['card_id']) ? intval($_POST['card_id']) : 0;

if ($card_id <= 0) {
    sendResponse(false, "Invalid card ID");
}

// 1. Check if card exists and is not scratched
$check_query = "SELECT reward_amount, is_scratched FROM scratch_cards WHERE id = ? AND customer_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $card_id, $customer_id);
$stmt->execute();
$res = $stmt->get_result();
$card = $res->fetch_assoc();

if (!$card) {
    sendResponse(false, "Scratch card not found");
}

if ($card['is_scratched']) {
    sendResponse(false, "Card already scratched");
}

$reward = $card['reward_amount'];

// 2. Start Transaction
$conn->begin_transaction();

try {
    // Update card
    $update_card = "UPDATE scratch_cards SET is_scratched = 1, scratched_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($update_card);
    $stmt->bind_param("i", $card_id);
    $stmt->execute();

    // Update wallet
    $update_wallet = "INSERT INTO customer_wallet (customer_id, balance) VALUES (?, ?) ON DUPLICATE KEY UPDATE balance = balance + ?";
    $stmt = $conn->prepare($update_wallet);
    $stmt->bind_param("iii", $customer_id, $reward, $reward);
    $stmt->execute();

    // Log transaction
    // (Optional: add a wallet_history table log here)

    $conn->commit();
    sendResponse(true, "Card scratched successfully", ["reward" => $reward]);
} catch (Exception $e) {
    $conn->rollback();
    sendResponse(false, "Failed to scratch card: " . $e->getMessage());
}
?>
