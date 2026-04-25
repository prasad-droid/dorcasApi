<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

// get headers
$headers = getallheaders();

$authHeader = $headers['Authorization'] ?? '';
$role = strtolower($headers['Role'] ?? '');

// validate headers
if (!$authHeader || !$role) {
    sendResponse(false, "Unauthorized: Missing token or role");
}

// extract token
$token = str_replace("Bearer ", "", $authHeader);

// validate role
if (!in_array($role, ['customer', 'vendor'])) {
    sendResponse(false, "Invalid role");
}

// decide table + join
if ($role === 'vendor') {
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

// get user
$user = $result->fetch_assoc();

// attach globally (important)
$GLOBALS['auth_user'] = $user;
$GLOBALS['auth_role'] = $role;
?>
