<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);
$name = trim($input['name'] ?? '');
$phone = trim($input['phone'] ?? '');
$role = $input['role'] ?? 'customer';
$latitude = $input['latitude'] ?? null;
$longitude = $input['longitude'] ?? null;

if (empty($name) || empty($phone)) {
    sendResponse(false, "Name and Phone Number are required");
}

// Check database based on role
if ($role === 'technician') {
    $table = 'vendors';
} else {
    $table = 'customers';
}

$stmt = $conn->prepare("SELECT * FROM $table WHERE phone = ? AND name = ? LIMIT 1");
$stmt->bind_param("ss", $phone, $name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Update location if provided
    if ($latitude && $longitude) {
        $update_loc = $conn->prepare("UPDATE $table SET latitude = ?, longitude = ? WHERE id = ?");
        $update_loc->bind_param("ddi", $latitude, $longitude, $user['id']);
        $update_loc->execute();
    }
    
    // Generate token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $token_table = ($role === 'technician') ? 'vendor_remember_tokens' : 'remember_tokens';
    $user_id_col = ($role === 'technician') ? 'vendor_id' : 'customer_id';
    
    $stmt = $conn->prepare("INSERT INTO $token_table ($user_id_col, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user['id'], $token, $expires);
    $stmt->execute();
    
    sendResponse(true, "Login successful", [
        "token" => $token,
        "user" => [
            "id" => $user['id'],
            "name" => $user['name'],
            "phone" => $user['phone']
        ]
    ]);
} else {
    sendResponse(false, "Invalid Name or Phone Number. Please check your details or register.");
}
?>
