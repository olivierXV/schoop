<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

// Ensure it's a POST request
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Capture and validate inputs
$userinfo = $_POST['userinfo'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($userinfo) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}
try {
    // Hash password securely
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Try updating teacher record (by email)
    $stmt = $conn->prepare("UPDATE teachers SET Password = :password WHERE Email = :userinfo");
    $stmt->execute(['password' => $hashedPassword, 'userinfo' => $userinfo]);

    // If no rows were updated, try updating student record (by LRN)
    if ($stmt->rowCount() === 0) {
        $stmt = $conn->prepare("UPDATE information SET Password = :password WHERE LRN = :userinfo");
        $stmt->execute(['password' => $hashedPassword, 'userinfo' => $userinfo]);
    }

    // Check if any update was successful
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found or password unchanged.']);
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal server error.']);
}
?>
