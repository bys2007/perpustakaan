<?php
$activeNav = 'pinjaman';
$pageTitle = 'Pinjaman Aktif';
require_once('petugas_common.php');

$currentQueryArray = $_GET;
unset($currentQueryArray['success'], $currentQueryArray['error'], $currentQueryArray['finish']);

function redirectPinjaman($params = array()) {
  global $currentQueryArray;
  $base = 'pinjaman.php';
  $query = array_merge($currentQueryArray, $params);
  $target = $base;
  if (!empty($query)) {
    $target .= '?' . http_build_query($query);
  }
  header("Location: " . $target);
  exit;
}

if ((isset($_GET['finish'])) && ($_GET['finish'] != "")) {
  mysql_select_db($database_koneksi, $koneksi);
  $updateSQL = sprintf("UPDATE peminjaman SET StatusPeminjaman=%s, TanggalPengembalian=%s WHERE PeminjamanID=%s AND StatusPeminjaman='dipinjam'",
                       GetSQLValueString('dikembalikan', "text"),
                       GetSQLValueString(date('Y-m-d'), "date"),
                       GetSQLValueString($_GET['finish'], "int"));
  $Result1 = mysql_query($updateSQL, $koneksi) or die(mysql_error());
  redirectPinjaman(array('success' => 'finish'));
}

$alertType = "";
$alertMessage = "";
if (isset($_GET['success']) && $_GET['success'] == 'finish') {
  $alertType = "alert-success";
  $alertMessage = "Peminjaman selesai dan status dikembalikan.";
}

mysql_select_db($database_koneksi, $koneksi);
$query_RactiveLoans = "SELECT peminjaman.*, `user`.Username, `user`.NamaLengkap, buku.Judul FROM peminjaman INNER JOIN `user` ON peminjaman.UserID = `user`.UserID INNER JOIN buku ON peminjaman.BukuID = buku.BukuID WHERE peminjaman.StatusPeminjaman = 'dipinjam' ORDER BY peminjaman.TanggalPeminjaman ASC";
$RactiveLoans = mysql_query($query_RactiveLoans, $koneksi) or die(mysql_error());
$row_RactiveLoans = mysql_fetch_assoc($RactiveLoans);
$totalRows_RactiveLoans = mysql_num_rows($RactiveLoans);

require('layout_header.php');
?>
    <main>
        <section class="content-section">
            <h2>Daftar Pinjaman Aktif</h2>
            <p>Pantau buku yang masih dipinjam dan konfirmasi pengembalian ketika buku dikembalikan.</p>
            <?php if ($alertMessage != "") { ?>
                <div class="alert <?php echo $alertType; ?>"><?php echo $alertMessage; ?></div>
            <?php } ?>
        </section>

        <section class="content-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Peminjam</th>
                        <th>Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($totalRows_RactiveLoans > 0) { ?>
                        <?php do { ?>
                        <tr>
                            <td><?php echo $row_RactiveLoans['PeminjamanID']; ?></td>
                            <td><?php echo $row_RactiveLoans['NamaLengkap'] ? $row_RactiveLoans['NamaLengkap'] : $row_RactiveLoans['Username']; ?></td>
                            <td><?php echo $row_RactiveLoans['Judul']; ?></td>
                            <td><?php echo $row_RactiveLoans['TanggalPeminjaman']; ?></td>
                            <td><span class="pill dipinjam">Dipinjam</span></td>
                            <td>
                                <a class="mini-button alt" href="pinjaman.php?finish=<?php echo $row_RactiveLoans['PeminjamanID']; ?>" onclick="return confirm('Konfirmasi buku sudah dikembalikan?');">Konfirmasi Kembali</a>
                            </td>
                        </tr>
                        <?php } while ($row_RactiveLoans = mysql_fetch_assoc($RactiveLoans)); ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6">Belum ada pinjaman aktif saat ini.</td>
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
if ($totalRows_RactiveLoans > 0) {
  mysql_data_seek($RactiveLoans, 0);
}
mysql_free_result($RactiveLoans);
?>
