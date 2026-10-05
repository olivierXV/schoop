<?php
session_start();
include 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the student is logged in
if (!isset($_SESSION['studentID'])) {
    die(json_encode(["error" => "Unauthorized access: Student not logged in."]));
}

$studentLRN = $_SESSION['studentID'];

// Fetch student's GradeLevel and Track
$gradeQuery = "SELECT GradeLevel, sec.Track 
               FROM information i
               JOIN sections sec ON i.SectionID = sec.SectionID
               WHERE i.LRN = ?";
$gradeStmt = $conn->prepare($gradeQuery);
if (!$gradeStmt) {
    die(json_encode(["error" => "SQL Prepare Error (Grade): " . $conn->error]));
}

$gradeStmt->bind_param('s', $studentLRN);
$gradeStmt->execute();
$gradeResult = $gradeStmt->get_result();

if ($gradeResult->num_rows === 0) {
    die(json_encode(["error" => "Student not found."]));
}

$row = $gradeResult->fetch_assoc();
$studentGrade = $row['GradeLevel'];
$studentTrack = $row['Track'];
$gradeStmt->close();

// Determine the current semester (1 = Q1 & Q2, 2 = Q3 & Q4)
$currentSemester = 2;

$quarter1 = ($currentSemester == 1) ? 'Q1' : 'Q3';
$quarter2 = ($currentSemester == 1) ? 'Q2' : 'Q4';

// Query to fetch relevant subjects and identify missing grades
$query = "
    SELECT s.SubjectName, s.Type, 
           g.ww1, g.ww2, g.ww3, g.ww4, 
           g.qz1, g.qz2, g.qz3, g.qz4, 
           g.pt1, g.pt2, g.pt3, g.pt4, 
           g.qa, g.quarter_grade
    FROM subject s
    JOIN sections sec ON s.Strand = sec.StrandName OR s.Strand = 'All'
    JOIN information i ON i.SectionID = sec.SectionID
    LEFT JOIN grades g ON g.SubjectID = s.SubjectID 
                       AND g.LRN = i.LRN 
                       AND g.quarter IN (?, ?) 
                       AND g.Grade = i.GradeLevel
    WHERE i.LRN = ?
    AND (s.Grade = ? OR s.Grade IS NULL)
    AND (s.Semester = ? OR s.Semester IS NULL)
    AND (s.Track = ? OR s.Track IS NULL)
    AND s.Type IN ('Core', 'Applied', 'Immersion', 'Specialized')
    ORDER BY FIELD(s.Type, 'Core', 'Applied', 'Immersion', 'Specialized'), s.SubjectName
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die(json_encode(["error" => "SQL Prepare Error: " . $conn->error]));
}

$stmt->bind_param('sssiss', $quarter1, $quarter2, $studentLRN, $studentGrade, $currentSemester, $studentTrack);
if (!$stmt->execute()) {
    die(json_encode(["error" => "SQL Execution Error: " . $stmt->error]));
}

$result = $stmt->get_result();

$missingGrades = [];

while ($row = $result->fetch_assoc()) {
    $missing = false;

    // Check if any grades are missing
    if (is_null($row['ww1']) || is_null($row['ww2']) || is_null($row['ww3']) || is_null($row['ww4']) ||
        is_null($row['qz1']) || is_null($row['qz2']) || is_null($row['qz3']) || is_null($row['qz4']) ||
        is_null($row['pt1']) || is_null($row['pt2']) || is_null($row['pt3']) || is_null($row['pt4']) ||
        is_null($row['qa']) || is_null($row['quarter_grade'])) {
        $missing = true;
    }

    if ($missing) {
        $missingGrades[] = [
            'SubjectName' => $row['SubjectName'],
            'Type' => $row['Type'],
            'ww1' => $row['ww1'],
            'ww2' => $row['ww2'],
            'ww3' => $row['ww3'],
            'ww4' => $row['ww4'],
            'qz1' => $row['qz1'],
            'qz2' => $row['qz2'],
            'qz3' => $row['qz3'],
            'qz4' => $row['qz4'],
            'pt1' => $row['pt1'],
            'pt2' => $row['pt2'],
            'pt3' => $row['pt3'],
            'pt4' => $row['pt4'],
            'qa' => $row['qa'],
            'quarter_grade' => $row['quarter_grade']
        ];
    }
}

// Close resources
$stmt->close();
$conn->close();

// Output missing grades as JSON
header('Content-Type: application/json');
echo json_encode($missingGrades, JSON_PRETTY_PRINT);
?>