<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$categories = [];
$query = "SELECT id, category_name, category_img, banner_img, meta_tittle, meta_keywords, meta_description, created_at, updated_at FROM categories WHERE deleted_at IS NULL";

$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    sendResponse(true, "Categories fetched successfully", $categories);
} else {
    sendResponse(false, "Failed to fetch categories: " . $conn->error);
}
?>
