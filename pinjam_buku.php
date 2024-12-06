<?php
session_start();
include_once './config/Connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['id_buku'], $_POST['tanggal_pinjam'])) {
  $id_buku = $_POST['id_buku'];
  $tanggal_pinjam = $_POST['tanggal_pinjam'];

  $result = $mysqli->query("SELECT kode FROM peminjaman ORDER BY id DESC LIMIT 1");
  $last_kode = $result->fetch_assoc();
  if ($last_kode) {
      preg_match('/\d+/', $last_kode['kode'], $matches);
      $kode_number = (int)$matches[0] + 1;
  } else {
      $kode_number = 1;
  }
  $kode = 'PINJAM-' . str_pad($kode_number, 5, '0', STR_PAD_LEFT);

  $mysqli->begin_transaction();

  try {
      $query = "SELECT stok FROM buku WHERE id = ?";
      $stmt = $mysqli->prepare($query);
      $stmt->bind_param('i', $id_buku);
      $stmt->execute();
      $result = $stmt->get_result();
      $book = $result->fetch_assoc();

      if ($book && $book['stok'] > 0) {
          $tanggal_kembali = null; // Set null by default
          $query = "INSERT INTO peminjaman (kode, id_user, id_buku, tanggal_pinjam, tanggal_kembali, status)
                    VALUES (?, ?, ?, ?, ?, '0')";
          $stmt = $mysqli->prepare($query);
          $stmt->bind_param('siiss', $kode, $user_id, $id_buku, $tanggal_pinjam, $tanggal_kembali);
          $stmt->execute();

          $new_stok = $book['stok'] - 1;
          $query = "UPDATE buku SET stok = ? WHERE id = ?";
          $stmt = $mysqli->prepare($query);
          $stmt->bind_param('ii', $new_stok, $id_buku);
          $stmt->execute();

          $mysqli->commit();

          $_SESSION['alert_message'] = [
              'type' => 'success',
              'message' => "Book borrowed successfully! Code: $kode"
          ];
          header("Location: pinjam_buku.php");
          exit();
      } else {
          $_SESSION['alert_message'] = [
              'type' => 'error',
              'message' => "Not enough stock to borrow this book."
          ];
          header("Location: pinjam_buku.php");
          exit();
      }
  } catch (Exception $e) {
      $mysqli->rollback();
      $_SESSION['alert_message'] = [
          'type' => 'error',
          'message' => "An error occurred while borrowing the book."
      ];
      header("Location: pinjam_buku.php");
      exit();
  }
}

$query = "SELECT id, judul, stok, cover FROM buku WHERE stok > 0";
$books_result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjam Buku</title>
    <link href="<?= BASE_URL . '/assets/dist/output.css' ?>" rel="stylesheet">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            max-width: 90%;
            max-height: 80%;
        }
        .modal img {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="bg-gray-100 flex min-h-screen w-full">
    <?php include './sidebar.php'; ?>
    <div class="flex-1 p-8">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Pinjam Buku</h2>

            <?php if (isset($_SESSION['alert_message'])): ?>
                <div class="p-2 mb-4 rounded <?= ($_SESSION['alert_message']['type'] === 'success') ? 'bg-green-600' : 'bg-red-600' ?> text-white">
                    <?= $_SESSION['alert_message']['message'] ?>
                    <?php unset($_SESSION['alert_message']); ?>
                </div>
            <?php endif; ?>

            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left border-b">Judul Buku</th>
                        <th class="px-4 py-2 text-left border-b">Stok</th>
                        <th class="px-4 py-2 text-left border-b">Cover</th>
                        <th class="px-4 py-2 text-left border-b">Tanggal Pinjam</th>
                        <th class="px-4 py-2 text-left border-b">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($book = $books_result->fetch_assoc()): ?>
                        <tr>
                            <form action="pinjam_buku.php" method="POST">
                                <td class="px-4 py-2"><?= htmlspecialchars($book['judul']) ?></td>
                                <td class="px-4 py-2"><?= $book['stok'] ?></td>
                                <td class="px-4 py-2">
                                    <img src="<?= htmlspecialchars($book['cover']) ?>" alt="Cover Image" class="w-20 h-auto cursor-pointer" onclick="openModal('<?= htmlspecialchars($book['cover']) ?>')">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="date" name="tanggal_pinjam" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="hidden" name="id_buku" value="<?= $book['id'] ?>">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Pinjam
                                    </button>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="imageModal" class="modal">
    <div class="modal-content">
        <img id="modalImage" src="" alt="Zoomed Image">
    </div>
</div>

<script>
    function openModal(imageUrl) {
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        modal.style.display = 'flex';
        modalImage.src = imageUrl;
    }

    window.onclick = function(event) {
        const modal = document.getElementById('imageModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
</script>

</body>
</html>
