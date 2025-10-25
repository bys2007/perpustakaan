<?php
$activeNav = 'peminjaman';
$pageTitle = 'Riwayat Peminjaman';
require_once('peminjam_common.php');

$currentQueryArray = $_GET;
unset($currentQueryArray['success'], $currentQueryArray['error'], $currentQueryArray['kembali']);

function redirectPeminjaman($params = array()) {
  global $currentQueryArray;
  $base = 'peminjaman.php';
  $query = array_merge($currentQueryArray, $params);
  $target = $base;
  if (!empty($query)) {
    $target .= '?' . http_build_query($query);
  }
  header("Location: " . $target);
  exit;
}

if ((isset($_GET['kembali'])) && ($_GET['kembali'] != "")) {
  mysql_select_db($database_koneksi, $koneksi);
  $updateSQL = sprintf("UPDATE peminjaman SET StatusPeminjaman=%s, TanggalPengembalian=%s WHERE PeminjamanID=%s AND UserID=%s AND StatusPeminjaman='dipinjam'",
                       GetSQLValueString('dikembalikan', "text"),
                       GetSQLValueString(date('Y-m-d'), "date"),
                       GetSQLValueString($_GET['kembali'], "int"),
                       GetSQLValueString($loggedInUserID, "int"));
  $Result1 = mysql_query($updateSQL, $koneksi) or die(mysql_error());

  redirectPeminjaman(array('success' => 'kembali'));
}

$alertType = "";
$alertMessage = "";
if (isset($_GET['success']) && $_GET['success'] == 'kembali') {
  $alertType = "alert-success";
  $alertMessage = "Status peminjaman diperbarui menjadi dikembalikan.";
}

mysql_select_db($database_koneksi, $koneksi);
$query_Rpeminjaman = sprintf("SELECT peminjaman.*, buku.Judul FROM peminjaman INNER JOIN buku ON peminjaman.BukuID = buku.BukuID WHERE peminjaman.UserID = %s ORDER BY peminjaman.PeminjamanID DESC",
                             GetSQLValueString($loggedInUserID, "int"));
$Rpeminjaman = mysql_query($query_Rpeminjaman, $koneksi) or die(mysql_error());
$row_Rpeminjaman = mysql_fetch_assoc($Rpeminjaman);
$totalRows_Rpeminjaman = mysql_num_rows($Rpeminjaman);

require('layout_header.php');
?>
    <main>
        <section class="content-section">
            <h2>Riwayat Peminjaman</h2>
            <p>Semua transaksi peminjaman Anda, termasuk permintaan yang masih diajukan.</p>
            <?php if ($alertMessage != "") { ?>
                <div class="alert <?php echo $alertType; ?>"><?php echo $alertMessage; ?></div>
            <?php } ?>
        </section>

        <section class="content-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Judul</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($totalRows_Rpeminjaman > 0) { ?>
                        <?php do { ?>
                        <tr>
                            <td><?php echo $row_Rpeminjaman['PeminjamanID']; ?></td>
                            <td><?php echo $row_Rpeminjaman['Judul']; ?></td>
                            <td><?php echo $row_Rpeminjaman['TanggalPeminjaman']; ?></td>
                            <td><?php echo $row_Rpeminjaman['TanggalPengembalian'] ? $row_Rpeminjaman['TanggalPengembalian'] : '-'; ?></td>
                            <td><span class="pill <?php echo strtolower($row_Rpeminjaman['StatusPeminjaman']); ?>"><?php echo ucfirst($row_Rpeminjaman['StatusPeminjaman']); ?></span></td>
                            <td>
                                <?php if ($row_Rpeminjaman['StatusPeminjaman'] == 'dipinjam') { ?>
                                    <a class="mini-button alt" href="peminjaman.php?kembali=<?php echo $row_Rpeminjaman['PeminjamanID']; ?>" onclick="return confirm('Konfirmasi pengembalian buku ini?');">Sudah Dikembalikan</a>
                                <?php } elseif ($row_Rpeminjaman['StatusPeminjaman'] == 'diajukan') { ?>
                                    <span class="mini-button" style="background-color:rgba(0,0,0,0.05); color:var(--text-color); cursor:default;">Menunggu Konfirmasi</span>
                                <?php } else { ?>
                                    <span class="mini-button" style="background-color:rgba(0,0,0,0.05); color:var(--text-color); cursor:default;">Selesai</span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } while ($row_Rpeminjaman = mysql_fetch_assoc($Rpeminjaman)); ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6">Belum ada riwayat peminjaman.</td>
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
if ($totalRows_Rpeminjaman > 0) {
  mysql_data_seek($Rpeminjaman, 0);
}
mysql_free_result($Rpeminjaman);
?>
