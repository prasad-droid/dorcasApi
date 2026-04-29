<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

// Fetch categories
$categories = [];

$catQuery = $conn->query("SELECT id, category_name, category_img FROM categories WHERE deleted_at IS NULL");

while ($cat = $catQuery->fetch_assoc()) {

    // Fetch services inside each category
    $services = [];

    $stmt = $conn->prepare("
        SELECT id, service_name, service_price, service_img 
        FROM services 
        WHERE category_id=? AND status=1 AND deleted_at IS NULL
    ");

    $stmt->bind_param("i", $cat['id']);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($srv = $res->fetch_assoc()) {
        $services[] = $srv;
    }

    $cat['services'] = $services;
    $categories[] = $cat;
}

sendResponse(true, "Services fetched", $categories);
?>