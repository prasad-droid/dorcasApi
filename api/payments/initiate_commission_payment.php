<?php
require_once '../config/db.php';
require_once '../config/payment_config.php';
require_once '../middleware/auth.php';
require_once './Crypto.php';

header('Content-Type: application/json');

// Get authenticated user from globals (set by auth.php)
$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];

if (!$user || $role !== 'technician') {
    echo json_encode(["status" => false, "message" => "Unauthorized: Technician access only"]);
    exit;
}

// Support JSON input
$input = json_decode(file_get_contents('php://input'), true);
if ($input) {
    $_POST = array_merge($_POST, $input);
}

$vendor_id = $user['id'];
$job_id = $_POST['job_id'] ?? null;
$amount = $_POST['amount'] ?? null;

if (!$amount || $amount <= 0) {
    echo json_encode(["status" => false, "message" => "Valid amount is required"]);
    exit;
}

// Generate unique Order ID - ENSURE NO HASH (#) OR INVALID CHARS
$order_id = "COMM_" . time() . "_" . $vendor_id;
$order_id = str_replace('#', '', $order_id); // Triple-safe check

// Debug logging
file_put_contents('payment_debug.log', "Time: " . date('Y-m-d H:i:s') . " | Order ID: " . $order_id . " | Amount: " . $amount . "\n", FILE_APPEND);

// If job_id is 'all', we might be paying multiple commissions
$payment_ids = ($job_id === 'all') ? 'all' : $job_id;

// Insert into ccav_orders table for tracking
$stmt = $conn->prepare("INSERT INTO ccav_orders (order_id, vendor_id, payment_ids, amount) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sisd", $order_id, $vendor_id, $payment_ids, $amount);
$stmt->execute();

// Prepare CCAvenue parameters
$parameters = [
    'merchant_id' => CCAV_MERCHANT_ID,
    'order_id' => $order_id,
    'amount' => $amount,
    'currency' => 'INR',
    'redirect_url' => CCAV_REDIRECT_URL,
    'cancel_url' => CCAV_CANCEL_URL,
    'language' => 'EN',
    'billing_name' => $user['name'] ?? 'Vendor',
];

// Use http_build_query for clean parameter string
$merchant_data = http_build_query($parameters);

$encrypted_data = encrypt_ccav($merchant_data, CCAV_WORKING_KEY);

// We return a URL to a bridge page that will auto-submit the POST request to CCAvenue
$payment_url = BASE_URL . "/payments/ccav_request_bridge.php?encRequest=" . $encrypted_data . "&access_code=" . CCAV_ACCESS_CODE;

echo json_encode([
    "status" => true,
    "payment_url" => $payment_url,
    "order_id" => $order_id
]);
?>