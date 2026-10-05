<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only allow admins (adjust this check as needed)
if (!isset($_SESSION['loggedInUser']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'db.php';

$message = '';

// Handle form submission for adding/updating announcements
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $announcement = trim($_POST['announcement'] ?? '');
    $type = $_POST['type'] ?? 'announcement';
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');

    if (empty($announcement)) {
        $message = "Announcement text cannot be empty.";
    } else {
        if ($id) {
            // Update an existing announcement
            $sql = "UPDATE announcements 
                    SET announcement = ?, type = ?, start_date = ?, end_date = ? 
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $announcement, $type, $start_date, $end_date, $id);
            if ($stmt->execute()) {
                $message = "Announcement updated successfully.";
            } else {
                $message = "Error updating announcement: " . $stmt->error;
            }
            $stmt->close();
        } else {
            // Insert a new announcement
            $sql = "INSERT INTO announcements (announcement, type, start_date, end_date)
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $announcement, $type, $start_date, $end_date);
            if ($stmt->execute()) {
                $message = "Announcement added successfully.";
            } else {
                $message = "Error adding announcement: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Handle announcement deletion
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $sql = "DELETE FROM announcements WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "Announcement deleted successfully.";
    } else {
        $message = "Error deleting announcement: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all announcements for display
$sql = "SELECT * FROM announcements ORDER BY updated_at DESC";
$result = $conn->query($sql);
$announcements = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Announcements</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .message { text-align: center; font-weight: bold; margin-bottom: 20px; }
        form { max-width: 600px; margin: 0 auto; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { padding: 8px 16px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ccc; }
        th, td { padding: 10px; text-align: left; }
        .delete-button { color: white; background-color: red; }
    </style>
</head>
<body>
    <h2>Edit Announcements</h2>
    
    <?php if ($message !== ''): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Announcement Form -->
    <form method="POST" action="">
        <input type="hidden" name="id" id="announcementId" value="">
        
        <label for="announcement">Announcement:</label>
        <textarea name="announcement" id="announcement" required></textarea>
        
        <label for="type">Type:</label>
        <select name="type" id="type">
            <option value="announcement">Announcement</option>
            <option value="holiday">Holiday</option>
        </select>
        
        <label for="start_date">Start Date (optional):</label>
        <input type="date" name="start_date" id="start_date">
        
        <label for="end_date">End Date (optional):</label>
        <input type="date" name="end_date" id="end_date">
        
        <button type="submit">Save Announcement</button>
    </form>

    <!-- Announcement Table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Announcement</th>
                <th>Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Updated At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($announcements)): ?>
                <tr><td colspan="7">No announcements found.</td></tr>
            <?php else: ?>
                <?php foreach ($announcements as $ann): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ann['id']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($ann['announcement'])); ?></td>
                        <td><?php echo htmlspecialchars($ann['type']); ?></td>
                        <td><?php echo htmlspecialchars($ann['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($ann['end_date']); ?></td>
                        <td><?php echo htmlspecialchars($ann['updated_at']); ?></td>
                        <td>
                            <button onclick="editAnnouncement(<?php echo $ann['id']; ?>, '<?php echo addslashes($ann['announcement']); ?>', '<?php echo $ann['type']; ?>', '<?php echo $ann['start_date']; ?>', '<?php echo $ann['end_date']; ?>')">Edit</button>
                            <a href="?delete=<?php echo $ann['id']; ?>" onclick="return confirm('Are you sure you want to delete this announcement?');">
                                <button class="delete-button">Delete</button>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        // Populate form for editing an announcement
        function editAnnouncement(id, announcement, type, startDate, endDate) {
            document.getElementById('announcementId').value = id;
            document.getElementById('announcement').value = announcement;
            document.getElementById('type').value = type;
            document.getElementById('start_date').value = startDate;
            document.getElementById('end_date').value = endDate;
            window.scrollTo(0, 0);
        }
    </script>
</body>
</html>
