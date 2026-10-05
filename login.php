<?php
// login.php - Handles teacher and student login
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

// Get user input
$userinfo = $_POST['userinfo'] ?? '';  // Email or LRN
$password = $_POST['password'] ?? '';

// Check if userinfo is empty
if (empty($userinfo)) {
    echo json_encode(['success' => false, 'message' => 'Please enter your email or LRN.']);
    exit;
}

// Identify whether input is email (teacher) or LRN (student)
if (filter_var($userinfo, FILTER_VALIDATE_EMAIL)) {
    $sql = "SELECT * FROM teachers WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userinfo);
    $userType = 'teacher';
} else {
    $sql = "SELECT * FROM information WHERE LRN = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userinfo);
    $userType = 'student';
}

$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

$row = $result->fetch_assoc();

// Redirect to "set password" if password is empty or null in the database
if (empty($password) || empty($row['Password'])) {
    $redirectUrl = "setpassword.html?userinfo=" . urlencode($userinfo);
    echo json_encode(['success' => false, 'redirect' => $redirectUrl]);
    exit;
}

// Verify the provided password
if (password_verify($password, $row['Password'])) {
    // Store session data
    if ($userType === 'teacher') {
        $_SESSION['loggedInUser'] = $row['Email'];
        $_SESSION['role'] = $row['Role'];  // Assuming there's a 'Role' column
        $_SESSION['teacherID'] = $row['TeacherID'];
        $_SESSION['subject'] = $row['Subject'] ?? '';
        echo json_encode(['success' => true, 'message' => 'Teacher Login Successful']);
    } else {
        $_SESSION['loggedInUser'] = $row['LRN'];
        $_SESSION['role'] = $row['Strand'] ?? '';
        $_SESSION['studentID'] = $row['LRN'];
        echo json_encode(['success' => true, 'message' => 'Student Login Successful']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid password.']);
}

$stmt->close();
$conn->close();
?>
