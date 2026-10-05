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
    AND (g.ww1 IS NULL AND g.ww2 IS NULL AND g.ww3 IS NULL AND g.ww4 IS NULL
         AND g.qz1 IS NULL AND g.qz2 IS NULL AND g.qz3 IS NULL AND g.qz4 IS NULL
         AND g.pt1 IS NULL AND g.pt2 IS NULL AND g.pt3 IS NULL AND g.pt4 IS NULL
         AND g.qa IS NULL AND g.quarter_grade IS NULL)
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
            'Type' => $row['Type']
        ];
    }
}

// Close resources
$stmt->close();
$conn->close();

// Output missing grades as JSON
header('Content-Type: application/json');
echo json_encode($missingGrades, JSON_PRETTY_PRINT);
