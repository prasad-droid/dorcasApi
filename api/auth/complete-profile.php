<?php
require "../middleware/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/response.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];

// =====================
// 📥 INPUT DATA
// =====================
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$address = trim($data['address'] ?? '');
$city = trim($data['city'] ?? '');
$state = trim($data['state'] ?? '');
$pincode = trim($data['pincode'] ?? '');
$latitude = $data['latitude'] ?? null;
$longitude = $data['longitude'] ?? null;

// Handle both keys safely
$services = $data['selectedServices'] ?? $data['services'] ?? [];

$referralInput = trim($data['referralCode'] ?? '');

$area = trim($data['area'] ?? '');
$landmark = trim($data['landmark'] ?? '');

// Merge address nicely
if ($area || $landmark) {
    $address = trim("$landmark, $area, $address");
}

// =====================
// 🔍 BASIC VALIDATION
// =====================
if (!$name) {
    sendResponse(false, "Name is required");
}

if ($role === 'technician' && empty($services)) {
    sendResponse(false, "Please select at least one service");
}

// =====================
// 🎁 REFERRAL SYSTEM
// =====================
function generateReferralCode($name)
{
    $clean = preg_replace("/[^a-zA-Z]/", "", $name);
    $prefix = strtoupper(substr($clean ?: "USER", 0, 4)); // fallback
    $random = rand(1000, 9999);
    return $prefix . $random;
}

$referredBy = null;

if (!empty($referralInput)) {

    // check customers
    $stmt = $conn->prepare("SELECT id FROM customers WHERE referral_code=?");
    $stmt->bind_param("s", $referralInput);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $referredBy = $row['id'];
    } else {
        // check vendors
        $stmt = $conn->prepare("SELECT id FROM vendors WHERE referral_code=?");
        $stmt->bind_param("s", $referralInput);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $referredBy = $row['id'];
        } else {
            sendResponse(false, "Invalid referral code");
        }
    }
}

// Generate unique referral code
do {
    $newReferralCode = generateReferralCode($name);

    $check1 = $conn->prepare("SELECT id FROM customers WHERE referral_code=?");
    $check1->bind_param("s", $newReferralCode);
    $check1->execute();
    $res1 = $check1->get_result();

    $check2 = $conn->prepare("SELECT id FROM vendors WHERE referral_code=?");
    $check2->bind_param("s", $newReferralCode);
    $check2->execute();
    $res2 = $check2->get_result();

} while ($res1->num_rows > 0 || $res2->num_rows > 0);


// =====================
// 👨‍🔧 TECHNICIAN
// =====================
if ($role === 'technician') {

    $stmt = $conn->prepare("
        UPDATE vendors 
        SET name=?,  address=?, city=?, state=?, pincode=?, latitude=?, longitude=?,  referral_code=?, referred_by=? 
        WHERE id=?
    ");

    $stmt->bind_param(
        "sssssddsii",
        $name,
        $address,
        $city,
        $state,
        $pincode,
        $latitude,
        $longitude,
        $newReferralCode,
        $referredBy,
        $user['id']
    );

    $stmt->execute();

    // =====================
    // 🛠 SAVE SERVICES
    // =====================
    $conn->query("DELETE FROM vendor_services WHERE vendor_id=" . $user['id']);

    if (!empty($services)) {
        $stmt = $conn->prepare("
    INSERT INTO vendor_services (vendor_id, service_id, category_id) 
    VALUES (?, ?, ?)
");

        foreach ($services as $service_id) {

            // Get category_id from services table
            $catStmt = $conn->prepare("SELECT category_id FROM services WHERE id=?");
            $catStmt->bind_param("i", $service_id);
            $catStmt->execute();
            $catResult = $catStmt->get_result();

            if ($catResult->num_rows == 0)
                continue;

            $catRow = $catResult->fetch_assoc();
            $category_id = $catRow['category_id'];

            // Insert properly
            $stmt->bind_param("iii", $user['id'], $service_id, $category_id);
            $stmt->execute();
        }
    }

    // =====================
// 👤 CUSTOMER
// =====================
} else {

    $stmt = $conn->prepare("
        UPDATE customers 
        SET name=?, email=?, address=?, city=?, latitude=?, longitude=?, referral_code=?, referred_by=? 
        WHERE id=?
    ");

    $stmt->bind_param(
        "ssssddssi",
        $name,
        $email,
        $address,
        $city,
        $latitude,
        $longitude,
        $newReferralCode,
        $referredBy,
        $user['id']
    );

    $stmt->execute();
}

sendResponse(true, "Profile updated successfully");
?>