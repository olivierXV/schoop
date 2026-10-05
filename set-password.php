<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db.php';

$lrn = $_SESSION['studentID'];
$message = '';

// Check if password is already set
$sql = "SELECT Password FROM information WHERE LRN = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $lrn);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($row && !empty($row['Password'])) {
    // If password is already set, redirect to dashboard
    header("Location: dashboard.php");
    exit;
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if (empty($password) || empty($confirmPassword)) {
        $message = "Both fields are required.";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
    } else {
        // Encrypt the password using bcrypt
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // Only allow students to set their password
        if (!isset($_SESSION['studentID'])) {
            header("Location: login.php");
            exit;
        }
        $updateSql = "UPDATE information SET Password = ? WHERE LRN = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ss", $hashedPassword, $lrn);
        if ($updateStmt->execute()) {
            $message = "Password set successfully. You may now log in.";
            // Optionally, redirect after success
            header("Location: login.php");
            exit;
        } else {
            $message = "Error setting password: " . $updateStmt->error;
        }
        $updateStmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Set Your Password</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 400px; margin: 0 auto; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 8px; margin-bottom: 15px; }
        button { padding: 10px 20px; font-size: 16px; cursor: pointer; }
        .message { text-align: center; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Set Your Password</h2>
    <?php if ($message !== ''): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="password">New Password:</label>
        <input type="password" name="password" id="password" required>
        
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
        
        <button type="submit">Set Password</button>
    </form>
</body>
</html>
