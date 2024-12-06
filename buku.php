<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  $_SESSION['alert_message'] = "You must be an admin to access this page.";
  header("Location: home.php");
  exit();
}

include_once './config/Connection.php';

$query = "SELECT * FROM buku";
$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_TITLE ?> | Buku</title>
    <link href="<?= BASE_URL . '/assets/dist/output.css' ?>" rel="stylesheet">
</head>
<body>
<div class="bg-gray-100 flex min-h-screen w-full">
    <?php include './sidebar.php'; ?>
    <div class="flex-1 p-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Books List</h2>

            <a href="add_book.php" class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 mb-4 inline-block">Add Buku</a>

            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Judul</th>
                        <th class="px-4 py-2 text-left">Deskripsi</th>
                        <th class="px-4 py-2 text-left">Stok</th>
                        <th class="px-4 py-2 text-left">Cover</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($book = $result->fetch_assoc()) { ?>
                    <tr class="border-b">
                        <td class="px-4 py-2"><?= htmlspecialchars($book['id']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($book['judul']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($book['deskripsi']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($book['stok']) ?></td>
                        <td class="px-4 py-2">
                            <a href="<?= htmlspecialchars($book['cover']) ?>" target="_blank">
                                <img src="<?= htmlspecialchars($book['cover']) ?>" alt="Cover Image" class="w-20 h-auto hover:opacity-80">
                            </a>
                        </td>
                        <td class="px-4 py-2">
                            <a href="edit_book.php?id=<?= $book['id'] ?>" class="text-blue-500 hover:text-blue-700">Edit</a>
                            |
                            <a href="delete_book.php?id=<?= $book['id'] ?>" class="text-red-500 hover:text-red-700">Delete</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
