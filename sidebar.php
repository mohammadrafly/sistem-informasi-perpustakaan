<div class="bg-blue-600 w-64 p-6">
    <h2 class="text-white text-2xl font-bold mb-6">Dashboard</h2>
    <ul>
        <li><a href="home.php" class="text-white hover:bg-blue-700 p-2 block rounded">Home</a></li>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <li><a href="buku.php" class="text-white hover:bg-blue-700 p-2 block rounded">Buku</a></li>
          <li><a href="user.php" class="text-white hover:bg-blue-700 p-2 block rounded">User</a></li>
          <li><a href="list_pinjaman.php" class="text-white hover:bg-blue-700 p-2 block rounded">List Pinjaman Buku</a></li>
        <?php else: ?>
          <li><a href="pinjam_buku.php" class="text-white hover:bg-blue-700 p-2 block rounded">Pinjam Buku</a></li>
          <li><a href="pinjaman_saya.php" class="text-white hover:bg-blue-700 p-2 block rounded">Pinjaman Saya</a></li>
        <?php endif; ?>
        <li><a href="logout.php" class="text-white hover:bg-blue-700 p-2 block rounded">Logout</a></li>
    </ul>
</div>
