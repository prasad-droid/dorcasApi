<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

if ($GLOBALS['auth_role'] !== 'customer') {
    sendResponse(false, "Unauthorized");
}

$customer_id = $GLOBALS['auth_user']['id'];

$query = "SELECT id, booking_id, reward_amount, is_scratched, created_at FROM scratch_cards WHERE customer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$cards = [];
while ($row = $result->fetch_assoc()) {
    $cards[] = $row;
}

sendResponse(true, "Scratch cards fetched", $cards);
?>
