<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  $_SESSION['alert_message'] = "You must be an admin to access this page.";
  header("Location: home.php");
  exit();
}

include_once './config/Connection.php';

// Get user ID from the query parameter
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Delete the user from the database
    $query = "DELETE FROM user WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $user_id);

    if ($stmt->execute()) {
        header("Location: user.php"); // Redirect back to user list after successful deletion
        exit();
    } else {
        echo "Failed to delete the user.";
    }
} else {
    header("Location: user.php");
    exit();
}
