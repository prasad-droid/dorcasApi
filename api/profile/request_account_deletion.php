<?php
require_once "../../config/database.php";
require_once "../../vendor/autoload.php";
require_once "../../config/jwt_config.php";
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Role");

$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
$role = isset($headers['Role']) ? strtolower($headers['Role']) : '';

if (!$authHeader || !$role || !in_array($role, ['customer', 'vendor'])) {
    http_response_code(401);
    echo json_encode(["status" => false, "message" => "Unauthorized access."]);
    exit;
}

$token = str_replace('Bearer ', '', $authHeader);

try {
    $decoded = JWT::decode($token, new Key(JWT_SECRET, JWT_ALGORITHM));
    $user_id = $decoded->data->id;

    $database = new Database();
    $db = $database->getConnection();

    $data = json_decode(file_get_contents("php://input"));
    $reason = isset($data->reason) ? $data->reason : "User requested from app settings";
    
    // Fetch user details
    $table = $role === 'vendor' ? 'vendors' : 'customers';
    $query = "SELECT name, phone, email FROM {$table} WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => false, "message" => "User not found."]);
        exit;
    }

    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

    // Check if a pending request already exists
    $checkQuery = "SELECT id FROM data_deletion_requests WHERE user_type = ? AND phone = ? AND status IN ('pending', 'processing')";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$role, $user['phone']]);
    if ($checkStmt->rowCount() > 0) {
        echo json_encode(["status" => true, "message" => "A deletion request is already pending for your account."]);
        exit;
    }

    // Insert into data_deletion_requests
    $insertQuery = "INSERT INTO data_deletion_requests (user_type, full_name, phone, email, reason, delete_types, status, ip_address) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $insertStmt = $db->prepare($insertQuery);
    $success = $insertStmt->execute([
        $role,
        $user['name'],
        $user['phone'],
        $user['email'] ?? null,
        $reason,
        'all',
        'pending',
        $ip_address
    ]);

    if ($success) {
        echo json_encode(["status" => true, "message" => "Account deletion request sent to admin successfully."]);
    } else {
        echo json_encode(["status" => false, "message" => "Failed to submit request. Please try again later."]);
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["status" => false, "message" => "Access denied.", "error" => $e->getMessage()]);
}
?>
