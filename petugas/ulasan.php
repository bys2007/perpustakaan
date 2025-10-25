<?php
$activeNav = 'ulasan';
$pageTitle = 'Ulasan Peminjam';
require_once('petugas_common.php');

mysql_select_db($database_koneksi, $koneksi);
$query_RlatestReviews = "SELECT ulasanbuku.*, `user`.Username, buku.Judul FROM ulasanbuku INNER JOIN `user` ON ulasanbuku.UserID = `user`.UserID INNER JOIN buku ON ulasanbuku.BukuID = buku.BukuID ORDER BY ulasanbuku.UlasanID DESC";
$RlatestReviews = mysql_query($query_RlatestReviews, $koneksi) or die(mysql_error());
$row_RlatestReviews = mysql_fetch_assoc($RlatestReviews);
$totalRows_RlatestReviews = mysql_num_rows($RlatestReviews);

require('layout_header.php');
?>
    <main>
        <section class="content-section">
            <h2>Ulasan Terbaru Peminjam</h2>
            <p>Gunakan informasi ini untuk menilai kualitas koleksi dan respon pembaca.</p>
        </section>

        <section class="content-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Peminjam</th>
                        <th>Buku</th>
                        <th>Rating</th>
                        <th>Ulasan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($totalRows_RlatestReviews > 0) { ?>
                        <?php do { ?>
                        <tr>
                            <td><?php echo $row_RlatestReviews['Username']; ?></td>
                            <td><?php echo $row_RlatestReviews['Judul']; ?></td>
                            <td><span class="pill"><?php echo $row_RlatestReviews['Rating']; ?>/5</span></td>
                            <td><?php echo nl2br(htmlentities($row_RlatestReviews['Ulasan'], ENT_QUOTES, 'UTF-8')); ?></td>
                        </tr>
                        <?php } while ($row_RlatestReviews = mysql_fetch_assoc($RlatestReviews)); ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="4">Belum ada ulasan yang direkam.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>
    </main>

<?php require('nav.php'); ?>
<?php require('layout_footer.php'); ?>
<?php
mysql_free_result($Ruser);
if ($totalRows_RlatestReviews > 0) {
  mysql_data_seek($RlatestReviews, 0);
}
mysql_free_result($RlatestReviews);
?>
