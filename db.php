<?php
// db.php
// Database connection
$host = "localhost";
$user = "root"; 
$password = ""; 
$dbname = "schoop";

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

?>