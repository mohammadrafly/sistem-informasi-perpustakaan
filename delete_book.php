<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  $_SESSION['alert_message'] = "You must be an admin to access this page.";
  header("Location: home.php");
  exit();
}

include_once './config/Connection.php'; // Assuming you have a database connection setup

// Get book ID from the query parameter
if (isset($_GET['id'])) {
    $book_id = $_GET['id'];

    // Fetch the book data to get the image path
    $query = "SELECT cover FROM buku WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $book_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Book not found, redirect or show an error
        header("Location: buku.php");
        exit();
    }

    $book = $result->fetch_assoc();
    $cover = $book['cover'];

    // Delete the book from the database
    $query = "DELETE FROM buku WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $book_id);

    if ($stmt->execute()) {
        // Delete the image from the server if it exists
        if (file_exists($cover)) {
            unlink($cover); // Delete the image file
        }
        header("Location: buku.php"); // Redirect back to buku list after successful delete
        exit();
    } else {
        $error = "Failed to delete the book. Please try again.";
    }
} else {
    header("Location: buku.php");
    exit();
}
