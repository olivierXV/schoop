<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

// Query the sections table to get SectionID and SectionName
$sections = [];
$sql = "SELECT SectionID, SectionName FROM sections ORDER BY SectionName";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row;
    }
}
$result->free();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize inputs
    $lrn           = trim($_POST['lrn'] ?? '');
    $firstName     = trim($_POST['firstName'] ?? '');
    $middleName    = trim($_POST['middleName'] ?? '');
    $lastName      = trim($_POST['lastName'] ?? '');
    $strand        = trim($_POST['strand'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $gradeLevel    = trim($_POST['gradeLevel'] ?? '');
    // Use the SectionID from the select, and optionally get SectionName from a hidden field.
    $sectionID     = trim($_POST['sectionID'] ?? '');
    $sectionName   = trim($_POST['sectionName'] ?? '');
    $schoolYear    = trim($_POST['schoolYear'] ?? '');

    // Check required fields
    if (empty($lrn) || empty($firstName) || empty($lastName) || empty($strand) ||
        empty($gradeLevel) || empty($sectionID) || empty($schoolYear)) {
        $message = "Please fill in all required fields.";
    } else {
        // If SectionName is empty, lookup from the sections array using SectionID
        if (empty($sectionName)) {
            foreach ($sections as $sec) {
                if ($sec['SectionID'] == $sectionID) {
                    $sectionName = $sec['SectionName'];
                    break;
                }
            }
        }
        
        // Insert student record with an empty password (or you could use a default value)
        $sql = "INSERT INTO information (LRN, FirstName, MiddleName, LastName, Strand, Specialization, GradeLevel, Section, Password, SectionID, SchoolYear)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, '', ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $message = "SQL Prepare Error: " . $conn->error;
        } else {
            $stmt->bind_param("ssssssssis", $lrn, $firstName, $middleName, $lastName, $strand, $specialization, $gradeLevel, $sectionName, $sectionID, $schoolYear);
            if ($stmt->execute()) {
                $message = "Student added successfully.";
            } else {
                $message = "Error adding student: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 600px; margin: 0 auto; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; margin-bottom: 15px; }
        button { padding: 10px 20px; font-size: 16px; cursor: pointer; }
        .message { text-align: center; font-weight: bold; color: green; }
        .error { text-align: center; font-weight: bold; color: red; }
    </style>
</head>
<body>
    <a style="float: right; font-size: large; color: white;" href="#" onclick="history.back()">
    <h2>Add New Student</h2>
    </a>
    <?php if ($message !== ''): ?>
        <p class="<?php echo (strpos($message, 'Error') === false) ? 'message' : 'error'; ?>"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="lrn">LRN:</label>
        <input type="text" name="lrn" id="lrn" maxlength="12" required>

        <label for="firstName">First Name:</label>
        <input type="text" name="firstName" id="firstName" required>

        <label for="middleName">Middle Name:</label>
        <input type="text" name="middleName" id="middleName">

        <label for="lastName">Last Name:</label>
        <input type="text" name="lastName" id="lastName" required>

        <label for="strand">Strand:</label>
        <select name="strand" id="strand" required>
            <option value="">Select Strand</option>
            <option value="HUMSS">HUMSS</option>
            <option value="GAS">GAS</option>
            <option value="HE">HE</option>
            <option value="ICT">ICT</option>
        </select>

        <label for="specialization">Specialization (optional):</label>
        <select name="specialization" id="specialization">
            <option value="">None</option>
            <option value="Programming">Programming</option>
            <option value="CSS">CSS</option>
            <option value="Combo 1">Combo 1</option>
            <option value="Combo 2">Combo 2</option>
        </select>

        <label for="gradeLevel">Grade Level:</label>
        <select name="gradeLevel" id="gradeLevel" required>
            <option value="">Select Grade</option>
            <option value="11">Grade 11</option>
            <option value="12">Grade 12</option>
        </select>

        <!-- Populate Section dropdown from database -->
        <label for="sectionID">Section:</label>
        <select name="sectionID" id="sectionID" required>
            <option value="">Select Section</option>
            <?php foreach ($sections as $sec): ?>
                <option value="<?php echo htmlspecialchars($sec['SectionID']); ?>">
                    <?php echo htmlspecialchars($sec['SectionName']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <!-- Hidden field to store SectionName automatically -->
        <input type="hidden" name="sectionName" id="sectionName">

        <!-- Remove Password input here; new page will handle setting password -->
        <label for="schoolYear">School Year:</label>
        <select name="schoolYear" id="schoolYear" required>
            <option value="">Select School Year</option>
            <option value="2023-2024">2023-2024</option>
            <option value="2024-2025">2024-2025</option>
            <option value="2025-2026">2025-2026</option>
        </select>

        <button type="submit">Add Student</button>
    </form>

    <script>
        // When section is selected, set hidden sectionName field automatically
        document.getElementById('sectionID').addEventListener('change', function() {
            const select = this;
            const selectedOption = select.options[select.selectedIndex];
            document.getElementById('sectionName').value = selectedOption.text;
        });
    </script>
</body>
</html>
