<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];
$user_id = $user['id'];

if ($role !== 'technician') {
    sendResponse(false, "Unauthorized");
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get currently selected service IDs
    $stmt = $conn->prepare("SELECT service_id FROM vendor_services WHERE vendor_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = (int)$row['service_id'];
    }
    
    sendResponse(true, "Services fetched", $services);
} 
elseif ($method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $service_ids = $input['services'] ?? [];

    if (!is_array($service_ids)) {
        sendResponse(false, "Invalid data format");
    }

    $conn->begin_transaction();

    try {
        // 1. Remove old services
        $stmt = $conn->prepare("DELETE FROM vendor_services WHERE vendor_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // 2. Insert new services
        if (!empty($service_ids)) {
            $insert_stmt = $conn->prepare("INSERT INTO vendor_services (vendor_id, service_id, category_id) VALUES (?, ?, ?)");
            foreach ($service_ids as $id) {
                // Get category_id for this service
                $cat_res = $conn->query("SELECT category_id FROM services WHERE id = $id");
                $cat_row = $cat_res->fetch_assoc();
                $cat_id = $cat_row['category_id'] ?? 0;

                $insert_stmt->bind_param("iii", $user_id, $id, $cat_id);
                $insert_stmt->execute();
            }
        }

        $conn->commit();
        sendResponse(true, "Services updated successfully");
    } catch (Exception $e) {
        $conn->rollback();
        sendResponse(false, "Update failed: " . $e->getMessage());
    }
}
?>
