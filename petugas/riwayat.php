<?php
$activeNav = 'riwayat';
$pageTitle = 'Riwayat Pengembalian';
require_once('petugas_common.php');

mysql_select_db($database_koneksi, $koneksi);
$query_RhistoryLoans = "SELECT peminjaman.*, `user`.Username, buku.Judul FROM peminjaman INNER JOIN `user` ON peminjaman.UserID = `user`.UserID INNER JOIN buku ON peminjaman.BukuID = buku.BukuID WHERE peminjaman.StatusPeminjaman = 'dikembalikan' ORDER BY peminjaman.TanggalPengembalian DESC LIMIT 30";
$RhistoryLoans = mysql_query($query_RhistoryLoans, $koneksi) or die(mysql_error());
$row_RhistoryLoans = mysql_fetch_assoc($RhistoryLoans);
$totalRows_RhistoryLoans = mysql_num_rows($RhistoryLoans);

require('layout_header.php');
?>
    <main>
        <section class="content-section">
            <h2>Riwayat Pengembalian</h2>
            <p>Catatan terakhir peminjaman yang sudah dikembalikan oleh peminjam.</p>
        </section>

        <section class="content-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Peminjam</th>
                        <th>Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($totalRows_RhistoryLoans > 0) { ?>
                        <?php do { ?>
                        <tr>
                            <td><?php echo $row_RhistoryLoans['PeminjamanID']; ?></td>
                            <td><?php echo $row_RhistoryLoans['Username']; ?></td>
                            <td><?php echo $row_RhistoryLoans['Judul']; ?></td>
                            <td><?php echo $row_RhistoryLoans['TanggalPeminjaman']; ?></td>
                            <td><?php echo $row_RhistoryLoans['TanggalPengembalian']; ?></td>
                        </tr>
                        <?php } while ($row_RhistoryLoans = mysql_fetch_assoc($RhistoryLoans)); ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="5">Belum ada catatan pengembalian.</td>
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
if ($totalRows_RhistoryLoans > 0) {
  mysql_data_seek($RhistoryLoans, 0);
}
mysql_free_result($RhistoryLoans);
?>
