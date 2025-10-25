<nav class="bottom-nav">
    <a href="petugas.php" class="<?php echo $activeNav == 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
    <a href="permintaan.php" class="<?php echo $activeNav == 'permintaan' ? 'active' : ''; ?>">Permintaan</a>
    <a href="pinjaman.php" class="<?php echo $activeNav == 'pinjaman' ? 'active' : ''; ?>">Pinjaman</a>
    <a href="riwayat.php" class="<?php echo $activeNav == 'riwayat' ? 'active' : ''; ?>">Riwayat</a>
    <a href="ulasan.php" class="<?php echo $activeNav == 'ulasan' ? 'active' : ''; ?>">Ulasan</a>
    <a href="<?php echo $logoutAction; ?>">Logout</a>
</nav>
