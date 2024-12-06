<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  $_SESSION['alert_message'] = "You must be an admin to access this page.";
  header("Location: home.php");
  exit();
}

include_once './config/Connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = $_POST['nama'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
  $role = $_POST['role'];

  // Insert the new user into the database
  $query = "INSERT INTO user (nama, email, password, role) VALUES (?, ?, ?, ?)";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('ssss', $nama, $email, $password, $role);  // Ensure the correct data types

  if ($stmt->execute()) {
      header("Location: user.php"); // Redirect to user list after successful insertion
      exit();
  } else {
      $error = "Failed to add the user. Please try again.";
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_TITLE ?> | Add User</title>
    <link href="<?= BASE_URL . '/assets/dist/output.css' ?>" rel="stylesheet">
</head>
<body>
<div class="bg-gray-100 flex min-h-screen w-full">
    <?php include './sidebar.php'; ?>
    <div class="flex-1 p-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Add New User</h2>

            <?php if (isset($error)) { ?>
                <div class="text-red-600 mb-4"><?= $error ?></div>
            <?php } ?>

            <!-- Add User Form -->
            <form method="POST" action="add_user.php" class="space-y-4">
                <div>
                    <label for="nama" class="block text-gray-700">Nama</label>
                    <input type="text" id="nama" name="nama" class="w-full p-2 border border-gray-300 rounded" required>
                </div>
                <div>
                    <label for="email" class="block text-gray-700">Email</label>
                    <input type="email" id="email" name="email" class="w-full p-2 border border-gray-300 rounded" required>
                </div>
                <div>
                    <label for="password" class="block text-gray-700">Password</label>
                    <input type="password" id="password" name="password" class="w-full p-2 border border-gray-300 rounded" required>
                </div>
                <div>
                    <label for="role" class="block text-gray-700">Role</label>
                    <select id="role" name="role" class="w-full p-2 border border-gray-300 rounded" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
