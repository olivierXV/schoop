<?php
header('Content-Type: application/json');
include 'db.php'; // Ensure proper database connection

// Receive JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Check if data is received
if (!$data) {
    echo json_encode(["error" => "No input received."]);
    exit;
}

// Validate required fields
$requiredFields = ['lrn', 'subjectID', 'quarter', 'schoolYear', 'grade'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        echo json_encode(["error" => "Missing required field: $field"]);
        exit;
    }
}

// Extract the necessary data
$lrn = $data['lrn'];
$subjectID = intval($data['subjectID']);
$quarter = $data['quarter'];
$schoolYear = $data['schoolYear'];
$grade = $data['grade'];

// Identify the grade field to update (e.g., ww1, ww2, etc.)
$allowedFields = ['ww1', 'ww2', 'ww3', 'ww4', 'qz1', 'qz2', 'qz3', 'qz4', 'pt1', 'pt2', 'pt3', 'pt4', 'qa'];
$fieldToUpdate = null;
$newValue = null;

foreach ($allowedFields as $field) {
    if (isset($data[$field])) {
        $fieldToUpdate = $field;
        $newValue = intval($data[$field]);
        break;
    }
}

if (!$fieldToUpdate) {
    echo json_encode(["error" => "No valid field to update."]);
    exit;
}

// Check if the record already exists
$checkQuery = "SELECT * FROM grades WHERE LRN = ? AND SubjectID = ? AND quarter = ? AND SchoolYear = ? AND Grade = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("sisss", $lrn, $subjectID, $quarter, $schoolYear, $grade);
$checkStmt->execute();
$result = $checkStmt->get_result();
$checkStmt->close();

// If record exists, update it; otherwise, insert a new one
if ($result->num_rows > 0) {
    // Update existing record
    $query = "UPDATE grades
              SET $fieldToUpdate = ?
              WHERE LRN = ?
                AND SubjectID = ?
                AND quarter = ?
                AND SchoolYear = ?
                AND Grade = ?";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(["error" => "Prepare statement failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("isisss", $newValue, $lrn, $subjectID, $quarter, $schoolYear, $grade);
    $stmt->execute();
} else {
    // Insert new record
    $query = "INSERT INTO grades (LRN, SubjectID, quarter, SchoolYear, Grade, $fieldToUpdate) 
              VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(["error" => "Prepare statement failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("sisssi", $lrn, $subjectID, $quarter, $schoolYear, $grade, $newValue);
    $stmt->execute();
}

if ($stmt->error) {
    echo json_encode(["error" => "Execute failed: " . $stmt->error]);
} else {
    echo json_encode(["success" => "Grade updated successfully."]);
}

$stmt->close();
$conn->close();
?>
