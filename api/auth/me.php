<?php
header("Content-Type: application/json");

include("../middleware/auth.php");

$user_id = $GLOBALS['user_id'];
$role = $GLOBALS['role'];

if($role == "customer"){
    $res = $conn->query("SELECT * FROM customers WHERE id='$user_id'");
}
elseif($role == "vendor"){
    $res = $conn->query("SELECT * FROM vendors WHERE id='$user_id'");
}
else{
    $res = $conn->query("SELECT * FROM users WHERE id='$user_id'");
}

$user = $res->fetch_assoc();

echo json_encode(["status"=>true,"user"=>$user,"role"=>$role]);
?>