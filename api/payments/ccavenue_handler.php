<?php
require_once '../config/db.php';
require_once '../config/payment_config.php';
require_once './Crypto.php';

$encResponse = $_POST['encResp'] ?? '';
$workingKey = CCAV_WORKING_KEY;
$decryptValues = explode('&', decrypt_ccav($encResponse, $workingKey));
$dataSize = sizeof($decryptValues);

$responseParams = [];
for ($i = 0; $i < $dataSize; $i++) {
    $information = explode('=', $decryptValues[$i]);
    if (count($information) == 2) {
        $responseParams[$information[0]] = $information[1];
    }
}

$order_status = $responseParams['order_status'] ?? '';
$order_id = $responseParams['order_id'] ?? '';
$tracking_id = $responseParams['tracking_id'] ?? '';
$bank_ref_no = $responseParams['bank_ref_no'] ?? '';

$status_message = "";
$is_success = false;

if ($order_status === "Success") {
    $is_success = true;
    $status_message = "Thank you for your payment. Your commission dues have been cleared.";
    
    // Update the bookings table
    // 1. Get the payment details from ccav_orders
    $stmt = $conn->prepare("SELECT payment_ids FROM ccav_orders WHERE order_id = ?");
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $order_info = $stmt->get_result()->fetch_assoc();
    
    if ($order_info) {
        $payment_ids = $order_info['payment_ids'];
        
        if ($payment_ids === 'all') {
            // Update all pending commissions for this vendor
            // We need vendor_id from ccav_orders
            $stmt_v = $conn->prepare("SELECT vendor_id FROM ccav_orders WHERE order_id = ?");
            $stmt_v->bind_param("s", $order_id);
            $stmt_v->execute();
            $vendor_id = $stmt_v->get_result()->fetch_assoc()['vendor_id'];
            
            $update_stmt = $conn->prepare("UPDATE bookings SET commission_status = 'paid' WHERE vendor_id = ? AND commission_status = 'pending'");
            $update_stmt->bind_param("i", $vendor_id);
            $update_stmt->execute();
            
            $update_vp = $conn->prepare("UPDATE vendor_payments SET status = 'paid' WHERE vendor_id = ? AND status = 'pending'");
            $update_vp->bind_param("i", $vendor_id);
            $update_vp->execute();
        } else {
            // Update specific job_id
            $update_stmt = $conn->prepare("UPDATE bookings SET commission_status = 'paid' WHERE id = ?");
            $update_stmt->bind_param("i", $payment_ids);
            $update_stmt->execute();
            
            $update_vp = $conn->prepare("UPDATE vendor_payments SET status = 'paid' WHERE booking_id = ? AND status = 'pending'");
            $update_vp->bind_param("i", $payment_ids);
            $update_vp->execute();
        }
    }
    
    // Update ccav_orders with tracking info
    $update_order = $conn->prepare("UPDATE ccav_orders SET tracking_id = ?, bank_ref_no = ?, status = 'Success' WHERE order_id = ?");
    if ($update_order) {
        $update_order->bind_param("sss", $tracking_id, $bank_ref_no, $order_id);
        $update_order->execute();
    }
} else if ($order_status === "Aborted") {
    $status_message = "The transaction has been aborted.";
} else if ($order_status === "Failure") {
    $status_message = "The transaction has failed.";
} else {
    $status_message = "Security Error. Illegal access detected.";
}

$return_url = $responseParams['merchant_param1'] ?? FRONTEND_URL;

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background-color: #f8f9fa; }
        .card { background: white; padding: 2rem; border-radius: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.05); text-align: center; max-width: 400px; width: 90%; }
        .icon { font-size: 4rem; margin-bottom: 1rem; }
        .success { color: #10b981; }
        .failure { color: #ef4444; }
        h1 { margin: 0 0 1rem; font-size: 1.5rem; color: #1e293b; }
        p { color: #64748b; line-height: 1.5; margin-bottom: 2rem; }
        .btn { display: inline-block; background: #2e85fd; color: white; padding: 0.75rem 2rem; border-radius: 1rem; text-decoration: none; font-weight: bold; transition: transform 0.2s; }
        .btn:active { transform: scale(0.95); }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon <?php echo $is_success ? 'success' : 'failure'; ?>">
            <?php echo $is_success ? '✓' : '✕'; ?>
        </div>
        <h1>Payment <?php echo $order_status; ?></h1>
        <p><?php echo $status_message; ?></p>
        
        <!-- Redirect back to app -->
        <a href="<?php echo $return_url; ?>/payment-callback?status=<?php echo $order_status; ?>&order_id=<?php echo $order_id; ?>" class="btn">Return to App</a>
        
        <script>
            // Auto redirect after 5 seconds
            setTimeout(function() {
                window.location.href = "<?php echo $return_url; ?>/payment-callback?status=<?php echo $order_status; ?>&order_id=<?php echo $order_id; ?>";
            }, 5000);
        </script>
    </div>
</body>
</html>
