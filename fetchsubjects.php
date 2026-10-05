<?php
session_start();
include 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the student is logged in
if (!isset($_SESSION['studentID'])) {
    die(json_encode(["error" => "Unauthorized access: Student not logged in."]));
}

$studentLRN = $_SESSION['studentID'];

// Fetch the student's GradeLevel and Track
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

$quarter1 = ($currentSemester == 1) ? 1 : 3; // Q1 or Q3
$quarter2 = ($currentSemester == 1) ? 2 : 4; // Q2 or Q4

// Fetch categorized subjects: Core, Applied/Immersion, Specialized
$query = "
    SELECT s.SubjectID, s.SubjectName, s.FormulaType, s.Semester, s.Type,
           ROUND(g1.quarter_grade) AS Q1_Grade, ROUND(g2.quarter_grade) AS Q2_Grade
    FROM subject s
    JOIN sections sec ON s.Strand = sec.StrandName OR s.Strand = 'All'
    JOIN information i ON i.SectionID = sec.SectionID
    LEFT JOIN grades g1 ON g1.SubjectID = s.SubjectID 
                        AND g1.LRN = i.LRN 
                        AND g1.quarter = ? 
                        AND g1.Grade = i.GradeLevel
    LEFT JOIN grades g2 ON g2.SubjectID = s.SubjectID 
                        AND g2.LRN = i.LRN 
                        AND g2.quarter = ? 
                        AND g2.Grade = i.GradeLevel
    WHERE i.LRN = ? 
    AND (s.Grade = ? OR s.Grade IS NULL)
    AND (s.Semester = ? OR s.Semester IS NULL)
    AND (s.Track = ? OR s.Track IS NULL)
    ORDER BY FIELD(s.Type, 'Core', 'Applied', 'Immersion', 'Specialized'), s.SubjectName
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die(json_encode(["error" => "SQL Prepare Error: " . $conn->error]));
}

$stmt->bind_param('iissis', $quarter1, $quarter2, $studentLRN, $studentGrade, $currentSemester, $studentTrack);
if (!$stmt->execute()) {
    die(json_encode(["error" => "SQL Execution Error: " . $stmt->error]));
}

$result = $stmt->get_result();

$subjects = [
    'Core' => [],
    'Applied/Immersion' => [],
    'Specialized' => []
];

// Categorize subjects
while ($row = $result->fetch_assoc()) {
    $subjectData = [
        'SubjectName' => $row['SubjectName'],
        'Q1_Grade' => $row['Q1_Grade'] ?? 'N/A',
        'Q2_Grade' => $row['Q2_Grade'] ?? 'N/A'
    ];
    if ($row['Type'] === 'Core') {
        $subjects['Core'][] = $subjectData;
    } elseif (in_array($row['Type'], ['Applied', 'Immersion'])) {
        $subjects['Applied/Immersion'][] = $subjectData;
    } else {
        $subjects['Specialized'][] = $subjectData;
    }
}

// Close connections
$stmt->close();
$conn->close();

// Add the current semester to the response
$subjects['currentSemester'] = $currentSemester;

// Output JSON with pretty print for easier debugging
header('Content-Type: application/json');
echo json_encode($subjects, JSON_PRETTY_PRINT);
