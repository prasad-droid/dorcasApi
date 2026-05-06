<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];
$user_id = $user['id'];

if ($role !== 'technician') {
    sendResponse(false, "Only technicians can submit KYC documents");
}

// Check current status
$check_stmt = $conn->prepare("SELECT kyc_status FROM vendors WHERE id = ?");
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$current_status = $check_stmt->get_result()->fetch_assoc()['kyc_status'] ?? 'not_submitted';

if ($current_status === 'pending' || $current_status === 'approved') {
    sendResponse(false, "Your KYC is already $current_status and cannot be modified.");
}

$doc_type = $_POST['doc_type'] ?? 'aadhar';

if (!isset($_FILES['document'])) {
    sendResponse(false, "No document file uploaded");
}

$file = $_FILES['document'];
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
if (!in_array($file['type'], $allowed_types)) {
    sendResponse(false, "Invalid file type. Only JPG, JPEG & PNG allowed.");
}

// Create uploads directory if not exists
$upload_dir = "../../uploads/kyc/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$file_name = "kyc_" . $user_id . "_" . time() . "." . $file_ext;
$target_path = $upload_dir . $file_name;

if (move_uploaded_file($file['tmp_name'], $target_path)) {
    // Corrected SQL: kyc_doc_type and kyc_doc_path
    $stmt = $conn->prepare("UPDATE vendors SET kyc_status='pending', kyc_doc_type=?, kyc_doc_path=? WHERE id=?");

    $document_url = "uploads/kyc/" . $file_name;
    
    // Match database enum 'aadhaar'
    $db_doc_type = ($doc_type === 'aadhar') ? 'aadhaar' : $doc_type;
    
    $stmt->bind_param("ssi", $db_doc_type, $document_url, $user_id);

    if ($stmt->execute()) {
        sendResponse(true, "KYC documents submitted successfully. Status: Pending");
    } else {
        sendResponse(false, "Failed to update database: " . $conn->error);
    }
} else {
    sendResponse(false, "Failed to save uploaded file.");
}
?>