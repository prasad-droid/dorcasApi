<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connection variables
$servername = "dorcasapi.psmcodes.in";
$username   = "u912243786_dorcas";
$password   = ";8@hebzZ";
$dbname     = "u912243786_dorcasApi";

// For compatibility with PDO code
$host = $servername;

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database Error: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
$conn->query("SET time_zone = '+5:30'"); 
echo "done"
?>