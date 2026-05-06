<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

try {
    $categories = [];
    
    $cat_query = "SELECT * FROM categories";
    $cat_result = $conn->query($cat_query);
    
    if (!$cat_result) {
        throw new Exception($conn->error);
    }

    while($cat = $cat_result->fetch_assoc()) {
        $cat_id = $cat['id'];
        
        // Fetch services for this category
        $services = [];
        $serv_query = "SELECT id, service_name, service_price as amount, service_img as image, service_desc as description FROM services WHERE category_id = $cat_id";
        $serv_result = $conn->query($serv_query);
        
        if ($serv_result) {
            while($serv = $serv_result->fetch_assoc()) {
                $services[] = $serv;
            }
        }
        
        $cat['services'] = $services;
        $categories[] = $cat;
    }
    
    sendResponse(true, "Categories with services fetched", $categories);
    
} catch (Exception $e) {
    sendResponse(false, "Error: " . $e->getMessage());
}
?>
