<?php
$activeNav = 'dashboard';
$pageTitle = 'Dashboard Petugas';
require_once('petugas_common.php');

mysql_select_db($database_koneksi, $koneksi);
$query_Rstats = "SELECT 
  (SELECT COUNT(*) FROM peminjaman WHERE StatusPeminjaman = 'diajukan') AS TotalDiajukan,
  (SELECT COUNT(*) FROM peminjaman WHERE StatusPeminjaman = 'dipinjam') AS TotalDipinjam,
  (SELECT COUNT(*) FROM peminjaman WHERE StatusPeminjaman = 'dikembalikan') AS TotalDikembalikan,
  (SELECT COUNT(*) FROM buku) AS TotalBuku,
  (SELECT COUNT(*) FROM koleksipribadi) AS TotalKoleksi,
  (SELECT COUNT(*) FROM ulasanbuku) AS TotalUlasan";
$Rstats = mysql_query($query_Rstats, $koneksi) or die(mysql_error());
$row_Rstats = mysql_fetch_assoc($Rstats);

require('layout_header.php');
?>
    <main>
        <section id="dashboard" class="content-section">
            <h2>Halo, <?php echo $row_Ruser['NamaLengkap'] ? $row_Ruser['NamaLengkap'] : $row_Ruser['Username']; ?></h2>
            <p>Kendalikan alur peminjaman, konfirmasi pengembalian, dan pantau aktivitas pembaca.</p>
        </section>

        <section class="content-section card-container">
            <div class="card">
                <h3>Permintaan Baru</h3>
                <p><?php echo $row_Rstats['TotalDiajukan']; ?> tiket</p>
            </div>
            <div class="card">
                <h3>Dipinjam Saat Ini</h3>
                <p><?php echo $row_Rstats['TotalDipinjam']; ?> buku</p>
            </div>
            <div class="card">
                <h3>Pengembalian Selesai</h3>
                <p><?php echo $row_Rstats['TotalDikembalikan']; ?> transaksi</p>
            </div>
            <div class="card">
                <h3>Koleksi & Ulasan</h3>
                <p><?php echo $row_Rstats['TotalBuku']; ?> buku</p>
                <span class="pill">Koleksi <?php echo $row_Rstats['TotalKoleksi']; ?></span>
                <span class="pill">Ulasan <?php echo $row_Rstats['TotalUlasan']; ?></span>
            </div>
        </section>
    </main>

<?php require('nav.php'); ?>
<?php require('layout_footer.php'); ?>
<?php
mysql_free_result($Ruser);
mysql_free_result($Rstats);
?>
