<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['loggedInUser'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$loggedInUser = $_SESSION['loggedInUser'];

// Check if the user is a teacher (by email) or a student (by LRN)
if (isset($_SESSION['role']) && $_SESSION['role'] === 'teacher' && isset($_SESSION['teacherID']) || $_SESSION['role'] === 'admin') {
    $teacherID = $_SESSION['teacherID'];

    // Query for teacher information (No SectionID filter)
    $sql = "SELECT t.TeacherID, t.LastName, t.MiddleName, t.FirstName, t.Email, t.Role, s.SubjectName, sec.SectionName
            FROM teacher_subjects ts
            JOIN sections sec ON sec.SectionID = ts.SectionID
            JOIN teachers t ON t.TeacherID = ts.TeacherID
            JOIN subject s ON ts.SubjectID = s.SubjectID
            WHERE ts.TeacherID = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'SQL prepare error: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("s", $teacherID);
} else {
    // Query for student information
    $sql = "SELECT LRN, FirstName, MiddleName, LastName, GradeLevel, Section, Strand 
            FROM information WHERE LRN = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'SQL prepare error: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("s", $loggedInUser);
}

// Execute the query and get the result
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    if ($_SESSION['role'] === 'teacher' || $_SESSION['role'] === 'admin') {
        // Fetch multiple subjects for a teacher
        $teacherData = [];
        while ($row = $result->fetch_assoc()) {
            $teacherData[] = [
                'TeacherID' => $row['TeacherID'],
                'FirstName' => $row['FirstName'],
                'MiddleName' => $row['MiddleName'],
                'LastName' => $row['LastName'],
                'SubjectName' => $row['SubjectName'],
                'SectionName' => $row['SectionName'],
                'Email' => $row['Email'],
                'Role' => $row['Role']
            ];
        }
        echo json_encode(['success' => true, 'data' => $teacherData]);
    } else {
        // Fetch single student record
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'LRN' => $row['LRN'],
            'FirstName' => $row['FirstName'],
            'MiddleName' => $row['MiddleName'],
            'LastName' => $row['LastName'],
            'GradeLevel' => $row['GradeLevel'],
            'Section' => $row['Section'],
            'Strand' => $row['Strand']
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}

// Clean up
$stmt->close();
$conn->close();
?>
