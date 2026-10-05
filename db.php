<?php
// db.php
// XAMPP login things
$host = "localhost";
$user = "root"; 
$password = ""; 
$dbname = "schoop";

$conn = new mysqli($host, $user, $password, $dbname);

// connection checker
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

?>