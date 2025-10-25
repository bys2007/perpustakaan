<nav class="bottom-nav">
    <a href="peminjam.php" class="<?php echo $activeNav == 'katalog' ? 'active' : ''; ?>">Katalog</a>
    <a href="koleksi.php" class="<?php echo $activeNav == 'koleksi' ? 'active' : ''; ?>">Koleksi</a>
    <a href="peminjaman.php" class="<?php echo $activeNav == 'peminjaman' ? 'active' : ''; ?>">Peminjaman</a>
    <a href="ulasan.php" class="<?php echo $activeNav == 'ulasan' ? 'active' : ''; ?>">Ulasan</a>
    <a href="<?php echo $logoutAction; ?>">Logout</a>
</nav>
