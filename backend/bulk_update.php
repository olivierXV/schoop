<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
include 'db.php';

// Capture incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['changes']) || !is_array($data['changes'])) {
    echo json_encode(["error" => "Invalid input."]);
    exit;
}

$changes = $data['changes'];
$successCount = 0;

// Get TeacherID from session, as string (if available)
$teacherID = isset($_SESSION['teacherID']) ? strval($_SESSION['teacherID']) : '';

foreach ($changes as $change) {
    // Required fields: lrn, subjectID, quarter, schoolYear, grade
    $required = ['lrn', 'subjectID', 'quarter', 'schoolYear', 'grade'];
    foreach ($required as $field) {
        if (!isset($change[$field]) || $change[$field] === '') {
            echo json_encode(["error" => "Missing required field: $field"]);
            exit;
        }
    }
    
    $lrn = $change['lrn'];
    $subjectID = intval($change['subjectID']);
    $quarter = $change['quarter'];
    $schoolYear = $change['schoolYear'];
    // Cast grade to string so it matches your ENUM definition
    $grade = strval($change['grade']);
    
    // Allowed raw grade fields that can be updated
    $allowedFields = ['ww1','ww2','ww3','ww4','qz1','qz2','qz3','qz4','pt1','pt2','pt3','pt4','qa'];
    $fieldToUpdate = null;
    $values = [];
    
    // We assume only one field is edited per change.
    foreach ($allowedFields as $field) {
        if (isset($change[$field])) {
            $fieldToUpdate = $field;
            $values[] = intval($change[$field]);
            break;
        }
    }
    
    if (!$fieldToUpdate) {
        echo json_encode(["error" => "No valid field to update."]);
        exit;
    }
    
    // Build the query:
    // We are inserting into 7 columns:
    // LRN, TeacherID, SubjectID, quarter, SchoolYear, Grade, and the updated field.
    // ON DUPLICATE KEY UPDATE will update only the specified field.
    $query = "
        INSERT INTO grades (LRN, TeacherID, SubjectID, quarter, SchoolYear, Grade, $fieldToUpdate)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE $fieldToUpdate = VALUES($fieldToUpdate)
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(["error" => "Prepare failed: " . $conn->error]);
        exit;
    }
    
    // Build type string for 7 parameters:
    // LRN: s, TeacherID: s, SubjectID: i, quarter: s, schoolYear: s, grade: s, newValue: i
    $types = "ssisssi";
    // $values has exactly one element because only one field is updated.
    $params = array_merge([$types, $lrn, $teacherID, $subjectID, $quarter, $schoolYear, $grade], $values);
    
    // Bind parameters using the spread operator.
    $stmt->bind_param(...$params);
    
    if ($stmt->execute()) {
        $successCount++;
    } else {
        echo json_encode(["error" => "Execution failed: " . $stmt->error]);
        exit;
    }
    
    $stmt->close();
}

$conn->close();
echo json_encode(["success" => "$successCount records updated."]);
?>
