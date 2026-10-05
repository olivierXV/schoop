<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

// Ensure either teacherID or studentID is set
if (!isset($_SESSION['teacherID']) && !isset($_SESSION['studentID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access: No valid session.']);
    exit();
}

// Initialize response
$response = ['success' => false, 'message' => 'Invalid user.'];

if (isset($_SESSION['teacherID'])) {
    $teacherID = $_SESSION['teacherID'];

    // Ensure teacherID is a string (CHAR(7) in schema)
    $query = "SELECT Role FROM teachers WHERE TeacherID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $teacherID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $teacher = $result->fetch_assoc();
        $_SESSION['role'] = $teacher['Role'];
        $response = [
            'success' => true,
            'role' => $teacher['Role'],
            'userType' => 'teacher'
        ];
    } else {
        $response['message'] = 'Teacher not found.';
    }
    $stmt->close();
}

elseif (isset($_SESSION['studentID'])) {
    $lrn = $_SESSION['studentID'];

    $query = "SELECT Strand FROM information WHERE LRN = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $lrn);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $info = $result->fetch_assoc();
        $_SESSION['strand'] = $info['Strand'];
        $response = [
            'success' => true,
            'strand' => $info['Strand'],
            'userType' => 'student'
        ];
    } else {
        $response['message'] = 'Student not found.';
    }
    $stmt->close();
}

// Output JSON response
echo json_encode($response);
$conn->close();
?>