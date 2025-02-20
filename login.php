<?php
session_start();
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
$userinfo = $_POST['userinfo'] ?? '';  // Email or LRN
$password = $_POST['password'] ?? '';

// Check if fields are empty
if (empty($userinfo) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
    exit;
}

// Check if userinfo is an email or LRN and query accordingly
if (filter_var($userinfo, FILTER_VALIDATE_EMAIL)) {
    // Query to check if email exists in the teachers table
    $sql = "SELECT * FROM teachers WHERE Email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userinfo);
} else {
    // Query to check if LRN exists in the information table
    $sql = "SELECT * FROM information WHERE LRN=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userinfo);  // Use string for LRN
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // If it's a teacher, verify the password
    if (isset($row['Email'])) {
        // Teacher login
        if (password_verify($password, $row['Password'])) {
            $_SESSION['loggedInUser'] = $row['Email'];
            $_SESSION['teacherID'] = $row['TeacherID']; // Store TeacherID
            echo json_encode(['success' => true, 'message' => 'Teacher Login Successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid Password']);
        }
    } else {
        // Student login (uses LRN)
        if (password_verify($password, $row['Password'])) {
            $_SESSION['loggedInUser'] = $row['LRN'];  // Store LRN for students
            $_SESSION['studentID'] = $row['LRN'];  // Store StudentID (LRN) for sessions
            echo json_encode(['success' => true, 'message' => 'Student Login Successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid Password']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}

$stmt->close();
$conn->close();
?>
