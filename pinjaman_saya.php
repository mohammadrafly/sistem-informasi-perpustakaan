<?php
session_start();
include_once './config/Connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT pb.id, b.judul, pb.tanggal_pinjam, pb.kode, pb.tanggal_kembali, pb.status
          FROM peminjaman pb
          JOIN buku b ON pb.id_buku = b.id
          WHERE pb.id_user = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjaman Saya</title>
    <link href="<?= BASE_URL . '/assets/dist/output.css' ?>" rel="stylesheet">
</head>
<body class="bg-gray-100 flex min-h-screen w-full">
  <?php include './sidebar.php'; ?>
      <div class="flex-1 p-8">
          <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Pinjaman Saya</h2>

          <?php if ($result->num_rows == 0): ?>
              <div class="bg-yellow-500 text-white p-4 rounded">
                  You have not borrowed any books yet.
              </div>
          <?php else: ?>
              <table class="min-w-full table-auto">
                  <thead>
                      <tr class="bg-gray-200">
                          <th class="px-4 py-2 text-left">Kode</th>
                          <th class="px-4 py-2 text-left">Judul</th>
                          <th class="px-4 py-2 text-left">Tanggal Pinjam</th>
                          <th class="px-4 py-2 text-left">Tanggal Kembali</th>
                          <th class="px-4 py-2 text-left">Status</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php while ($row = $result->fetch_assoc()): ?>
                          <tr class="border-b">
                              <td class="px-4 py-2"><?= htmlspecialchars($row['kode']) ?></td>
                              <td class="px-4 py-2"><?= htmlspecialchars($row['judul']) ?></td>
                              <td class="px-4 py-2"><?= htmlspecialchars($row['tanggal_pinjam']) ?></td>
                              <td class="px-4 py-2"><?= htmlspecialchars($row['tanggal_kembali'] ?? 'Belum Kembali') ?></td>
                              <td class="px-4 py-2">
                                  <?php if (htmlspecialchars($row['status']) == 0): ?>
                                      <span class="bg-blue-500 text-white px-2 py-1 rounded">Dipinjam</span>
                                  <?php elseif ($row['status'] == 1): ?>
                                      <span class="bg-yellow-500 text-white px-2 py-1 rounded">Jatuh Tempo</span>
                                  <?php elseif ($row['status'] == 2): ?>
                                      <span class="bg-red-500 text-white px-2 py-1 rounded">Terlambat</span>
                                  <?php elseif ($row['status'] == 3): ?>
                                      <span class="bg-green-500 text-white px-2 py-1 rounded">Dikembalikan</span>
                                  <?php else: ?>
                                      <span class="bg-gray-500 text-white px-2 py-1 rounded">Status tidak diketahui</span>
                                  <?php endif; ?>
                              </td>
                          </tr>
                      <?php endwhile; ?>
                  </tbody>
              </table>
          <?php endif; ?>
          </div>
      </div>

</body>
</html>
