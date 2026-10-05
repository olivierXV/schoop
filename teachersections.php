<?php
session_start();
include 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the user is a teacher
if (!isset($_SESSION['teacherID'])) {
    die(json_encode(["error" => "Unauthorized access: Not a teacher."]));
}

$teacherID = $_SESSION['teacherID'];

// Fetch unique sections assigned to the teacher
$query = "
    SELECT DISTINCT sec.SectionID, sec.SectionName
    FROM teacher_subjects ts
    JOIN sections sec ON ts.SectionID = sec.SectionID
    WHERE ts.TeacherID = ?
    ORDER BY sec.SectionName;
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die(json_encode(["error" => "SQL Prepare Error: " . $conn->error]));
}

$stmt->bind_param('s', $teacherID);
$stmt->execute();
$result = $stmt->get_result();

$sections = [];
while ($row = $result->fetch_assoc()) {
    $sections[] = $row;
}

$stmt->close();
$conn->close();

// Return sections as JSON
header('Content-Type: application/json');
echo json_encode($sections);
?>
