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

    // Fetch the book data from the database
    $query = "SELECT * FROM buku WHERE id = ?";
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
} else {
    header("Location: buku.php");
    exit();
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $stok = $_POST['stok'];
    $cover = $book['cover']; // Keep the current cover image by default

    // Handle file upload if a new image is uploaded
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        // Delete the old image if it exists
        if (file_exists($cover)) {
            unlink($cover); // Delete the old image
        }

        // Generate a unique hash for the new image name
        $imageTmpName = $_FILES['cover']['tmp_name'];
        $imageExtension = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        $imageHashName = md5(uniqid(rand(), true)) . '.' . $imageExtension;

        // Define the storage directory and move the uploaded file
        $storageDir = 'storage/';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0777, true); // Create the directory if it doesn't exist
        }

        $cover = $storageDir . $imageHashName;
        move_uploaded_file($imageTmpName, $cover);
    }

    // Update the book data in the database
    $query = "UPDATE buku SET judul = ?, deskripsi = ?, stok = ?, cover = ? WHERE id = ?";
    $stmt = $mysqli->prepare($query);

    // Correct bind_param with 5 arguments: 4 string parameters and 1 integer (for 'id')
    $stmt->bind_param('ssisi', $judul, $deskripsi, $stok, $cover, $book_id);

    if ($stmt->execute()) {
        header("Location: buku.php"); // Redirect back to books list after successful update
        exit();
    } else {
        $error = "Failed to update the book. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_TITLE ?> | Edit Book</title>
    <link href="<?= BASE_URL . '/assets/dist/output.css' ?>" rel="stylesheet">
</head>
<body>
<div class="bg-gray-100 flex min-h-screen w-full">
    <?php include './sidebar.php'; ?>
    <div class="flex-1 p-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Edit Book</h2>

            <?php if (isset($error)) { ?>
                <div class="text-red-600 mb-4"><?= $error ?></div>
            <?php } ?>

            <!-- Edit Book Form -->
            <form method="POST" action="edit_book.php?id=<?= $book['id'] ?>" class="space-y-4" enctype="multipart/form-data">
                <div>
                    <label for="judul" class="block text-gray-700">Judul</label>
                    <input type="text" id="judul" name="judul" class="w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($book['judul']) ?>" required>
                </div>
                <div>
                    <label for="deskripsi" class="block text-gray-700">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" class="w-full p-2 border border-gray-300 rounded" rows="4" required><?= htmlspecialchars($book['deskripsi']) ?></textarea>
                </div>
                <div>
                    <label for="stok" class="block text-gray-700">Stok</label>
                    <input type="number" id="stok" name="stok" class="w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($book['stok']) ?>" required>
                </div>
                <div>
                    <label for="cover" class="block text-gray-700">Cover Image (Current: <?= basename($book['cover']) ?>)</label>
                    <input type="file" id="cover" name="cover" class="w-full p-2 border border-gray-300 rounded">
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">Update Book</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
