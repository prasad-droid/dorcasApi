<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, "Invalid request method");
}

$category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
$subcategory_name = isset($_POST['subcategory_name']) ? trim($_POST['subcategory_name']) : '';
$subcategory_img = isset($_POST['subcategory_img']) ? trim($_POST['subcategory_img']) : '';
$meta_tittle = isset($_POST['meta_tittle']) ? trim($_POST['meta_tittle']) : '';
$meta_keywords = isset($_POST['meta_keywords']) ? trim($_POST['meta_keywords']) : '';
$meta_description = isset($_POST['meta_description']) ? trim($_POST['meta_description']) : '';

if (empty($subcategory_name) || $category_id === 0) {
    sendResponse(false, "Subcategory name and category_id are required");
}

$created_at = date('Y-m-d H:i:s');

$stmt = $conn->prepare("INSERT INTO subcategories (category_id, subcategory_name, subcategory_img, meta_tittle, meta_keywords, meta_description, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssss", $category_id, $subcategory_name, $subcategory_img, $meta_tittle, $meta_keywords, $meta_description, $created_at);

if ($stmt->execute()) {
    sendResponse(true, "Subcategory added successfully", ["id" => $conn->insert_id]);
} else {
    sendResponse(false, "Failed to add subcategory: " . $stmt->error);
}
?>
