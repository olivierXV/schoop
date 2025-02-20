<?php
session_start();
header('Content-Type: application/json');

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

// Ensure the session contains either teacherID or studentID
if (!isset($_SESSION['teacherID']) && !isset($_SESSION['studentID'])) {
    echo json_encode(['success' => false, 'message' => 'Teacher or Student ID not set in session']);
    exit();
}

// Initialize response
$response = ['success' => false, 'message' => ''];

if (isset($_SESSION['teacherID'])) {
    // Teacher is logged in, fetch role
    $teacherID = $_SESSION['teacherID'];
    $query = "SELECT Role FROM teachers WHERE TeacherID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $teacherID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $teacher = $result->fetch_assoc();
        $_SESSION['role'] = $teacher['Role'];  // Store role in session
        $response = [
            'success' => true,
            'role' => $teacher['Role'],
            'userType' => 'teacher'
        ];
    } else {
        $response = ['success' => false, 'message' => 'Teacher not found'];
    }

} elseif (isset($_SESSION['studentID'])) {
    // Student is logged in, fetch strand
    $lrn = $_SESSION['studentID'];
    $query = "SELECT Strand FROM information WHERE LRN = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $lrn);  // Use string for LRN
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $info = $result->fetch_assoc();
        $_SESSION['strand'] = $info['Strand'];  // Store strand in session
        $response = [
            'success' => true,
            'strand' => $info['Strand'],
            'userType' => 'student'
        ];
    } else {
        $response = ['success' => false, 'message' => 'Student not found'];
    }
}

// Return the data as a JSON response
echo json_encode($response);

// Close the database connection
$stmt->close();
$conn->close();
?>
