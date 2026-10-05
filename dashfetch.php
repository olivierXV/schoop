<?php
session_start();
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$isStudent = isset($_SESSION['studentID']);
$isTeacher = isset($_SESSION['teacherID']);
if (!$isStudent && !$isTeacher) {
    die(json_encode(["error" => "Unauthorized access: No valid session."]));
}

$grade = isset($_GET['grade']) ? htmlspecialchars($_GET['grade']) : null;
$semester = isset($_GET['semester']) ? intval($_GET['semester']) : 1;
$validQuarters = ($semester == 1) ? ['Q1', 'Q2'] : ['Q3', 'Q4'];
$quarter = (isset($_GET['quarter']) && in_array($_GET['quarter'], $validQuarters)) ? htmlspecialchars($_GET['quarter']) : $validQuarters[0];
$sectionID = isset($_GET['section']) ? intval($_GET['section']) : null;
$schoolYear = isset($_GET['schoolYear']) ? htmlspecialchars($_GET['schoolYear']) : null;

// Fetch available school years
if (isset($_GET['fetch']) && $_GET['fetch'] === 'schoolYears') {
    $query = "SELECT DISTINCT SchoolYear FROM grades ORDER BY SchoolYear DESC";
    $result = $conn->query($query);
    $schoolYears = [];
    while ($row = $result->fetch_assoc()) {
        $schoolYears[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($schoolYears);
    exit();
}

// Fetch sections for teachers
if (isset($_GET['fetch']) && $_GET['fetch'] === 'sections' && $isTeacher) {
    $teacherID = $_SESSION['teacherID'];
    $query = "
        SELECT DISTINCT sec.SectionID, sec.SectionName
        FROM teacher_subjects ts
        JOIN sections sec ON ts.SectionID = sec.SectionID
        WHERE ts.TeacherID = ?
        ORDER BY sec.SectionName;
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) die(json_encode(["error" => "SQL Error (Sections): " . $conn->error]));
    $stmt->bind_param('s', $teacherID);
    $stmt->execute();
    $result = $stmt->get_result();

    $sections = [];
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row;
    }

    $stmt->close();
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode($sections);
    exit();
}

// Fetch grades for students
if ($isStudent) {
    $studentLRN = $_SESSION['studentID'];

    $query = "
        SELECT s.SubjectName, sec.Track, ROUND(g.quarter_grade) AS quarter_grade
        FROM subject s
        JOIN sections sec ON s.Strand = sec.StrandName OR s.Strand = 'All'
        JOIN information i ON i.SectionID = sec.SectionID
        LEFT JOIN grades g ON g.SubjectID = s.SubjectID
                           AND g.LRN = i.LRN
                           AND g.quarter = ?
                           AND g.Grade = ?
        WHERE i.LRN = ?
          AND (s.Strand = sec.StrandName OR s.Strand = 'All')
          AND (s.Track = sec.Track OR s.Track IS NULL)
          AND (s.Grade = ? OR s.Grade IS NULL)
          AND (s.Semester = ? OR s.Semester IS NULL)
        ORDER BY FIELD(s.Type, 'Core', 'Applied', 'Immersion', 'Specialized'), s.SubjectName;
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) die(json_encode(["error" => "SQL Error (Student Grades): " . $conn->error]));
    $stmt->bind_param('sssss', $quarter, $grade, $studentLRN, $grade, $semester);
}

// Fetch grades for teachers
if ($isTeacher) {
    $teacherID = $_SESSION['teacherID'];

$query = "
    SELECT 
        i.LastName, i.MiddleName, i.FirstName, 
        s.SubjectName, 
        g.ww1, g.ww2, g.ww3, g.ww4,
        g.qz1, g.qz2, g.qz3, g.qz4,
        g.ww_qz_average,
        g.pt1, g.pt2, g.pt3, g.pt4,
        g.pt_average,
        g.qa,
        ROUND(g.quarter_grade) AS quarter_grade
    FROM teacher_subjects ts
    JOIN subject s ON ts.SubjectID = s.SubjectID
    JOIN information i ON i.SectionID = ts.SectionID AND i.GradeLevel = ts.Grade
    LEFT JOIN grades g 
        ON g.SubjectID = s.SubjectID 
        AND g.LRN = i.LRN 
        AND g.quarter = ?
        AND g.SchoolYear = ?
    WHERE 
        ts.TeacherID = ? 
        AND ts.SectionID = ?
    ORDER BY s.SubjectName, i.LastName, i.FirstName;
";

    $stmt = $conn->prepare($query);
    if (!$stmt) die(json_encode(["error" => "SQL Error (Teacher Grades): " . $conn->error]));
    $stmt->bind_param('sssi', $quarter, $schoolYear, $teacherID, $sectionID);}

// Execute and return results
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);
?>
