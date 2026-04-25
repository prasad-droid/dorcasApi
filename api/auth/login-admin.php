<?php
header("Content-Type: application/json");

include("../config/database.php");

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$res = $conn->query("SELECT * FROM users WHERE email='$email'");

if($res->num_rows == 0){
    echo json_encode(["status"=>false,"message"=>"User not found"]);
    exit;
}

$user = $res->fetch_assoc();

if(!password_verify($password, $user['password'])){
    echo json_encode(["status"=>false,"message"=>"Wrong password"]);
    exit;
}

$token = bin2hex(random_bytes(32));

$conn->query("INSERT INTO auth_tokens (user_id, role, token)
              VALUES ('".$user['id']."','admin','$token')");

echo json_encode([
    "status"=>true,
    "token"=>$token,
    "role"=>"admin"
]);
?>