<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$subcategories = [];
$categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;

if ($categoryId) {
    $stmt = $conn->prepare("SELECT id, category_id, subcategory_name, subcategory_img, meta_tittle, meta_keywords, meta_description, created_at, updated_at FROM subcategories WHERE category_id = ? AND deleted_at IS NULL");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query = "SELECT id, category_id, subcategory_name, subcategory_img, meta_tittle, meta_keywords, meta_description, created_at, updated_at FROM subcategories WHERE deleted_at IS NULL";
    $result = $conn->query($query);
}

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row;
    }
    sendResponse(true, "Subcategories fetched successfully", $subcategories);
} else {
    sendResponse(false, "Failed to fetch subcategories: " . $conn->error);
}
?>
