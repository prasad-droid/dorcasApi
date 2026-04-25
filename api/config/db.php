<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connection variables
$servername = "localhost";
$username   = "u322583024_dorcas";
$password   = "Dorcas@2k25";
$dbname     = "u322583024_dorcas";

// For compatibility with PDO code
$host = $servername;

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database Error: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
$conn->query("SET time_zone = '+5:30'"); 
?>