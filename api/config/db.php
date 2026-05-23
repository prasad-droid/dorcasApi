<?php
// Comprehensive CORS Headers - MUST BE AT THE VERY TOP
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Role, role, authorization, token");
header("Access-Control-Max-Age: 86400");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Parse JSON input
$input = file_get_contents("php://input");
if ($input) {
    $data = json_decode($input, true);
    if ($data) {
        foreach ($data as $key => $value) {
            $_POST[$key] = $value;
        }
    }
}

// Local Configuration
$servername = "localhost"; // Note: This usually needs the Hostinger MySQL IP if connecting remotely
$username = "root";
$password = "";
$dbname = "u322583024_dorcas";

// $servername = "localhost"; // Note: This usually needs the Hostinger MySQL IP if connecting remotely
// $username = "u103892271_dorcas";
// $password = "Dorcas@#3&45";
// $dbname = "u103892271_dorcas";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(["status" => false, "message" => "Database connection failed"]);
    exit;
}
$conn->set_charset("utf8mb4");
$conn->query("SET time_zone = '+5:30'");
?>