<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include_once './config/Connection.php';

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_TITLE ?> | Dashboard</title>
    <link href="<?= BASE_URL . '/assets/dist/output.css' ?>" rel="stylesheet">
</head>
<body>
<div class="bg-gray-100 flex min-h-screen w-full">
    <?php include './sidebar.php'; ?>
    <div class="flex-1 p-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Welcome, <?= htmlspecialchars($email) ?></h2>

            <div class="mb-4">
                <p class="text-gray-600">You are logged in with the email: <?= htmlspecialchars($email) ?></p>
            </div>

            <?php if (isset($_SESSION['alert_message'])): ?>
            <div class="bg-red-500 text-white p-4 rounded-lg mb-6 text-center">
                <?= $_SESSION['alert_message'] ?>
                <?php unset($_SESSION['alert_message']) ?>
            </div>
            <?php endif; ?>

            <div class="flex justify-end">
                <a href="logout.php" class="bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700">Logout</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
