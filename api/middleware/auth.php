<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$headers = getallheaders();

$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
$roleHeader = strtolower($headers['Role'] ?? $headers['role'] ?? '');

// Validate headers
if (!$authHeader || !$roleHeader) {
    sendResponse(false, "Unauthorized: Missing token or role");
}

// Extract token
$token = str_replace("Bearer ", "", $authHeader);

// Validate role
if (!in_array($roleHeader, ['customer', 'technician'])) {
    sendResponse(false, "Invalid role");
}

// Query based on role
if ($roleHeader === 'technician') {
    $query = "
        SELECT v.* FROM vendor_remember_tokens t
        JOIN vendors v ON v.id = t.vendor_id
        WHERE t.token='$token' AND t.expires_at > NOW()
        LIMIT 1
    ";
} else {
    $query = "
        SELECT c.* FROM remember_tokens t
        JOIN customers c ON c.id = t.customer_id
        WHERE t.token='$token' AND t.expires_at > NOW()
        LIMIT 1
    ";
}

$result = $conn->query($query);

if (!$result || $result->num_rows == 0) {
    sendResponse(false, "Unauthorized: Invalid or expired token");
}

// Store user globally
$user = $result->fetch_assoc();

$GLOBALS['auth_user'] = $user;
$GLOBALS['auth_role'] = $roleHeader;
?>