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

    // Fetch the user data from the database
    $query = "SELECT * FROM user WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // User not found, redirect or show an error
        header("Location: user.php");
        exit();
    }

    $user = $result->fetch_assoc();
} else {
    header("Location: user.php");
    exit();
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Update the user data in the database
    $query = "UPDATE user SET nama = ?, email = ?, role = ? WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sssi', $nama, $email, $role, $user_id);

    if ($stmt->execute()) {
        header("Location: user.php"); // Redirect to user list after successful update
        exit();
    } else {
        $error = "Failed to update the user. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_TITLE ?> | Edit User</title>
    <link href="<?= BASE_URL . '/assets/dist/output.css' ?>" rel="stylesheet">
</head>
<body>
<div class="bg-gray-100 flex min-h-screen w-full">
    <?php include './sidebar.php'; ?>
    <div class="flex-1 p-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Edit User</h2>

            <?php if (isset($error)) { ?>
                <div class="text-red-600 mb-4"><?= $error ?></div>
            <?php } ?>

            <!-- Edit User Form -->
            <form method="POST" action="edit_user.php?id=<?= $user['id'] ?>" class="space-y-4">
                <div>
                    <label for="nama" class="block text-gray-700">Nama</label>
                    <input type="text" id="nama" name="nama" class="w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($user['nama']) ?>" required>
                </div>
                <div>
                    <label for="email" class="block text-gray-700">Email</label>
                    <input type="email" id="email" name="email" class="w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div>
                    <label for="role" class="block text-gray-700">Role</label>
                    <select id="role" name="role" class="w-full p-2 border border-gray-300 rounded" required>
                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
