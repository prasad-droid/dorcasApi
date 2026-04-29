<?php
require "../middleware/auth.php";

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];

// Remove sensitive info if any
unset($user['otp']);
unset($user['otp_expires_at']);

sendResponse(true, "User fetched successfully", [
    "user" => $user,
    "role" => $role
]);
?>