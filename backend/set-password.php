<?php
require_once "db_connection.php"; // Include your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userinfo = $_POST["userinfo"];
    $newPassword = $_POST["newPassword"];

    if (empty($userinfo) || empty($newPassword)) {
        echo json_encode(["success" => false, "message" => "Missing required fields."]);
        exit;
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Check if user is a teacher or student
    $query = "UPDATE students SET password = ? WHERE lrn = ? AND (password IS NULL OR password = '')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $hashedPassword, $userinfo);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $query = "UPDATE teachers SET password = ? WHERE email = ? AND (password IS NULL OR password = '')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $hashedPassword, $userinfo);
        $stmt->execute();
    }

    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update password."]);
    }

    $stmt->close();
    $conn->close();
}
?>
