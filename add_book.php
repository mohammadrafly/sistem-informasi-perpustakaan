<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  $_SESSION['alert_message'] = "You must be an admin to access this page.";
  header("Location: home.php");
  exit();
}

include_once './config/Connection.php'; // Assuming you have a database connection setup

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $stok = $_POST['stok'];

    // Handle file upload
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        // Generate a unique hash for the image name
        $imageTmpName = $_FILES['cover']['tmp_name'];
        $imageExtension = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        $imageHashName = md5(uniqid(rand(), true)) . '.' . $imageExtension;

        // Define the storage directory and move the uploaded file
        $storageDir = 'storage/';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0777, true); // Create the directory if it doesn't exist
        }

        $imagePath = $storageDir . $imageHashName;

        if (move_uploaded_file($imageTmpName, $imagePath)) {
            // Insert the new book into the database
            $query = "INSERT INTO buku (judul, deskripsi, stok, cover) VALUES (?, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('ssis', $judul, $deskripsi, $stok, $imagePath);

            if ($stmt->execute()) {
                header("Location: books.php"); // Redirect back to books list after successful insert
                exit();
            } else {
                $error = "Failed to add the book. Please try again.";
            }
        } else {
            $error = "Failed to upload the image. Please try again.";
        }
    } else {
        $error = "No image uploaded or there was an error uploading the image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_TITLE ?> | Add Book</title>
    <link href="<?= BASE_URL . '/assets/dist/output.css' ?>" rel="stylesheet">
</head>
<body>
<div class="bg-gray-100 flex min-h-screen w-full">
    <?php include './sidebar.php'; ?>
    <div class="flex-1 p-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Add New Book</h2>

            <?php if (isset($error)) { ?>
                <div class="text-red-600 mb-4"><?= $error ?></div>
            <?php } ?>

            <form method="POST" action="add_book.php" class="space-y-4" enctype="multipart/form-data">
                <div>
                    <label for="judul" class="block text-gray-700">Judul</label>
                    <input type="text" id="judul" name="judul" class="w-full p-2 border border-gray-300 rounded" required>
                </div>
                <div>
                    <label for="deskripsi" class="block text-gray-700">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" class="w-full p-2 border border-gray-300 rounded" rows="4" required></textarea>
                </div>
                <div>
                    <label for="stok" class="block text-gray-700">Stok</label>
                    <input type="number" id="stok" name="stok" class="w-full p-2 border border-gray-300 rounded" required>
                </div>
                <div>
                    <label for="cover" class="block text-gray-700">Cover Image</label>
                    <input type="file" id="cover" name="cover" class="w-full p-2 border border-gray-300 rounded" required>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">Add Book</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
