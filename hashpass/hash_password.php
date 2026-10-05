<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Get form data
$userinfo = $_POST['userinfo'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';

// Check if fields are empty
if (empty($userinfo) || empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
    exit();
}

// Hash the new password
$hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

// Update the password in the database
if (filter_var($userinfo, FILTER_VALIDATE_EMAIL)) {
    // Update password for email in the teachers table
    $sql = "UPDATE teachers SET Password=? WHERE Email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $hashedPassword, $userinfo);
} else {
    // Update password for LRN in the information table
    $sql = "UPDATE information SET Password=? WHERE LRN=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $hashedPassword, $userinfo);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating password']);
}

$stmt->close();
$conn->close();
?>
