<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "u322583024_dorcas";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$phone = "9022428111";
$res = $conn->query("SELECT id FROM vendors WHERE phone='$phone'");
$vendor = $res->fetch_assoc();
if ($vendor) {
    $user_id = $vendor['id'];
    echo "Vendor ID: $user_id\n";
    $assigned = $conn->query("SELECT COUNT(*) n FROM bookings WHERE vendor_id=$user_id")->fetch_assoc()['n'];
    echo "Assigned Bookings: $assigned\n";
    $completed = $conn->query("SELECT COUNT(*) n FROM bookings WHERE vendor_id=$user_id AND status='completed'")->fetch_assoc()['n'];
    echo "Completed Bookings: $completed\n";
    
    // Check vendor_booking_requests
    $vbr = $conn->query("SELECT COUNT(*) n FROM vendor_booking_requests WHERE vendor_id=$user_id")->fetch_assoc()['n'];
    echo "VBR Requests: $vbr\n";
} else {
    echo "Vendor not found";
}
?>
