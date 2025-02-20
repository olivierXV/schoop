<?php
session_start();
if (!isset($_SESSION['loggedInUser'])) {
    header("Location: login.html");
    exit();
}
?>
