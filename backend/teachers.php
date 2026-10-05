<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
include 'db.php';

// Ensure only admins can access this
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["error" => "Unauthorized access. Admins only."]);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacherID = trim($_POST['TeacherID'] ?? '');
    $firstName = trim($_POST['FirstName'] ?? '');
    $middleName = trim($_POST['MiddleName'] ?? '');
    $lastName = trim($_POST['LastName'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $role = ($_POST['Role'] === 'admin') ? 'admin' : 'teacher';

    // Validate required fields
    if (empty($teacherID) || empty($firstName) || empty($lastName) || empty($email)) {
        echo json_encode(["error" => "TeacherID, First Name, Last Name, and Email are required."]);
        exit;
    }

    // Check for duplicate TeacherID or Email
    $checkQuery = "SELECT * FROM teachers WHERE TeacherID = ? OR Email = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $teacherID, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(["error" => "TeacherID or Email already exists."]);
        exit;
    }
    $stmt->close();

    // Insert teacher with NULL password
    $insertQuery = "INSERT INTO teachers (TeacherID, FirstName, MiddleName, LastName, Email, Password, Role) 
                    VALUES (?, ?, ?, ?, ?, NULL, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ssssss", $teacherID, $firstName, $middleName, $lastName, $email, $role);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Teacher added successfully."]);
    } else {
        echo json_encode(["error" => "Error adding teacher: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit;
}

// Fetch all teachers (for display)
$fetchQuery = "SELECT TeacherID, FirstName, MiddleName, LastName, Email, Role, created_at FROM teachers ORDER BY created_at DESC";
$result = $conn->query($fetchQuery);

$teachers = [];
while ($row = $result->fetch_assoc()) {
    $teachers[] = $row;
}

echo json_encode(["teachers" => $teachers]);
$conn->close();
