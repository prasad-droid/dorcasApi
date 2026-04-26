<?php
header("Content-Type: application/json");

include("../middleware/auth.php");

$user = $GLOBALS['auth_user'];
$role = $GLOBALS['auth_role'];

echo json_encode(["status"=>true,"user"=>$user,"role"=>$role]);
?>