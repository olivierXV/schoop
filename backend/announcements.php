<?php
header('Content-Type: application/json');
include 'db.php'; // Ensure the correct path

// Fetch announcements and holidays
$query = "SELECT announcement AS text, type, start_date, end_date FROM announcements WHERE start_date IS NOT NULL";
$result = $conn->query($query);

$announcements = [];
$holidays = [];

while ($row = $result->fetch_assoc()) {
    if ($row['type'] === 'holiday') {
        $holidays[] = $row;
    } else {
        $announcements[] = $row;
    }
}

echo json_encode([
    'announcements' => $announcements,
    'holidays' => $holidays
]);

$conn->close();
?>
