<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, "Invalid request method");
}

$category_name = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';
$category_img = isset($_POST['category_img']) ? trim($_POST['category_img']) : '';
$banner_img = isset($_POST['banner_img']) ? trim($_POST['banner_img']) : '';
$meta_tittle = isset($_POST['meta_tittle']) ? trim($_POST['meta_tittle']) : '';
$meta_keywords = isset($_POST['meta_keywords']) ? trim($_POST['meta_keywords']) : '';
$meta_description = isset($_POST['meta_description']) ? trim($_POST['meta_description']) : '';

if (empty($category_name)) {
    sendResponse(false, "Category name is required");
}

$stmt = $conn->prepare("INSERT INTO categories (category_name, category_img, banner_img, meta_tittle, meta_keywords, meta_description) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $category_name, $category_img, $banner_img, $meta_tittle, $meta_keywords, $meta_description);

if ($stmt->execute()) {
    sendResponse(true, "Category added successfully", ["id" => $conn->insert_id]);
} else {
    sendResponse(false, "Failed to add category: " . $stmt->error);
}
?>
