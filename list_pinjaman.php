<?php
session_start();
include_once './config/Connection.php';

// Periksa apakah admin sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

// Tangani pengembalian buku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_id'])) {
    $return_id = intval($_POST['return_id']);

    // Mulai transaksi untuk memastikan konsistensi data
    $mysqli->begin_transaction();
    try {
        // Perbarui status peminjaman
        $update_query = "UPDATE peminjaman SET status = '3', tanggal_kembali = NOW() WHERE id = ?";
        $stmt = $mysqli->prepare($update_query);
        $stmt->bind_param('i', $return_id);
        $stmt->execute();

        // Ambil ID buku dari peminjaman
        $book_query = "SELECT id_buku FROM peminjaman WHERE id = ?";
        $stmt = $mysqli->prepare($book_query);
        $stmt->bind_param('i', $return_id);
        $stmt->execute();
        $stmt->bind_result($book_id);
        $stmt->fetch();
        $stmt->close();

        // Perbarui stok buku
        $stock_query = "UPDATE buku SET stok = stok + 1 WHERE id = ?";
        $stmt = $mysqli->prepare($stock_query);
        $stmt->bind_param('i', $book_id);
        $stmt->execute();

        // Commit transaksi
        $mysqli->commit();
        $success_message = "Buku berhasil dikembalikan dan stok diperbarui.";
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        $mysqli->rollback();
        $error_message = "Terjadi kesalahan saat mengembalikan buku: " . $e->getMessage();
    }
}

// Tangani pencarian
$search_kode = isset($_GET['search_kode']) ? trim($_GET['search_kode']) : null;

$query = "SELECT pb.id, pb.kode, b.judul, u.nama AS peminjam, pb.tanggal_pinjam, pb.tanggal_kembali, pb.status
          FROM peminjaman pb
          JOIN buku b ON pb.id_buku = b.id
          JOIN user u ON pb.id_user = u.id";

if ($search_kode) {
    $query .= " WHERE pb.kode LIKE ?";
    $stmt = $mysqli->prepare($query);
    $like_kode = "%$search_kode%";
    $stmt->bind_param('s', $like_kode);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $mysqli->query($query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peminjaman</title>
    <link href="<?= BASE_URL . '/assets/dist/output.css' ?>" rel="stylesheet">
</head>
<body class="bg-gray-100 flex min-h-screen w-full">
    <?php include './sidebar.php'; ?>
    <div class="flex-1 p-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Daftar Peminjaman</h2>

            <form method="GET" class="mb-4">
                <div class="flex items-center">
                    <input
                        type="text"
                        name="search_kode"
                        placeholder="Cari berdasarkan kode peminjaman"
                        value="<?= htmlspecialchars($search_kode ?? '') ?>"
                        class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                    >
                    <button
                        type="submit"
                        class="ml-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        Cari
                    </button>
                </div>
            </form>

            <?php if (isset($success_message)): ?>
                <div class="bg-green-500 text-white p-4 rounded mb-4">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php elseif (isset($error_message)): ?>
                <div class="bg-red-500 text-white p-4 rounded mb-4">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <?php if ($result->num_rows == 0): ?>
                <div class="bg-yellow-500 text-white p-4 rounded">
                    Tidak ada data peminjaman<?= $search_kode ? " dengan kode \"$search_kode\"" : "" ?>.
                </div>
            <?php else: ?>
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-4 py-2 text-left">Kode</th>
                            <th class="px-4 py-2 text-left">Judul Buku</th>
                            <th class="px-4 py-2 text-left">Peminjam</th>
                            <th class="px-4 py-2 text-left">Tanggal Pinjam</th>
                            <th class="px-4 py-2 text-left">Tanggal Kembali</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="px-4 py-2"><?= htmlspecialchars($row['kode']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['judul']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['peminjam']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['tanggal_pinjam']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['tanggal_kembali'] ?? 'Belum Kembali') ?></td>
                                <td class="px-4 py-2">
                                    <?php if ($row['status'] == 0): ?>
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
                                <td class="px-4 py-2">
                                    <?php if ($row['status'] == 0 || $row['status'] == 1 || $row['status'] == 2): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="return_id" value="<?= htmlspecialchars($row['id']) ?>">
                                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-700">Kembalikan</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-gray-500">Tidak ada aksi</span>
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
