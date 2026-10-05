<?php
require 'db.php';

$userinfo = $_POST['userinfo'];
$password = $_POST['password'];

// Check if user exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :userinfo OR lrn = :userinfo");
$stmt->execute(['userinfo' => $userinfo]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
    exit;
}

if (empty($user['password'])) {
    echo json_encode(['success' => true, 'blank_password' => true]);
    exit;
}

// Verify password
if ($password === $user['password']) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
}
?>
