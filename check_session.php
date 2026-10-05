<?php
// check_session.php - Ensure session consistency across requests
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Check if a user is logged in
if (isset($_SESSION['loggedInUser'])) {
    echo json_encode(['loggedIn' => true, 'role' => $_SESSION['role'] ?? '']);
} else {
    echo json_encode(['loggedIn' => false]);
}
?>
