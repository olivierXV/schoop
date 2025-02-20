<?php
session_start();
header('Content-Type: application/json');

// Redirect if not logged in
if (!isset($_SESSION['loggedInUser'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

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

// Get logged-in user's email or LRN
$loggedInUser = $_SESSION['loggedInUser'];

// Check if logged-in user is a teacher or a student
if (filter_var($loggedInUser, FILTER_VALIDATE_EMAIL)) {
    // Teacher: query teachers table
    $sql = "SELECT TeacherID, FirstName, LastName, Email, Role FROM teachers WHERE Email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $loggedInUser);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'TeacherID' => $row['TeacherID'],
            'FirstName' => $row['FirstName'],
            'LastName' => $row['LastName'],
            'Email' => $row['Email'],
            'Role' => $row['Role']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Teacher not found']);
    }

} else {
    // Student: query information table using LRN
    $sql = "SELECT LRN, FirstName, MiddleName, LastName, Grade, Section, Strand FROM information WHERE LRN=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $loggedInUser);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'LRN' => $row['LRN'],
            'FirstName' => $row['FirstName'],
            'MiddleName' => $row['MiddleName'],
            'LastName' => $row['LastName'],
            'Grade' => $row['Grade'],
            'Section' => $row['Section'],
            'Strand' => $row['Strand']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
    }
}

$stmt->close();
$conn->close();
?>
