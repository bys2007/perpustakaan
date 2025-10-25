<?php
$activeNav = 'permintaan';
$pageTitle = 'Permintaan Peminjaman';
require_once('petugas_common.php');

$currentQueryArray = $_GET;
unset($currentQueryArray['success'], $currentQueryArray['error'], $currentQueryArray['cancel']);

function redirectWithMessage($params = array()) {
  global $currentQueryArray;
  $base = 'permintaan.php';
  $query = array_merge($currentQueryArray, $params);
  $target = $base;
  if (!empty($query)) {
    $target .= '?' . http_build_query($query);
  }
  header("Location: " . $target);
  exit;
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "approveLoan")) {
  $targetLoan = isset($_POST['PeminjamanID']) ? $_POST['PeminjamanID'] : "";
  if ($targetLoan == "") {
    redirectWithMessage(array('error' => 'approve_general'));
  }

  mysql_select_db($database_koneksi, $koneksi);
  $updateSQL = sprintf("UPDATE peminjaman SET StatusPeminjaman=%s, TanggalPeminjaman=%s WHERE PeminjamanID=%s AND StatusPeminjaman='diajukan'",
                       GetSQLValueString('dipinjam', "text"),
                       GetSQLValueString(date('Y-m-d'), "date"),
                       GetSQLValueString($targetLoan, "int"));
  $Result1 = mysql_query($updateSQL, $koneksi) or die(mysql_error());
  redirectWithMessage(array('success' => 'approve'));
}

if ((isset($_GET['cancel'])) && ($_GET['cancel'] != "")) {
  mysql_select_db($database_koneksi, $koneksi);
  $deleteSQL = sprintf("DELETE FROM peminjaman WHERE PeminjamanID=%s AND StatusPeminjaman='diajukan'",
                       GetSQLValueString($_GET['cancel'], "int"));
  $Result1 = mysql_query($deleteSQL, $koneksi) or die(mysql_error());
  redirectWithMessage(array('success' => 'cancel'));
}

$alertType = "";
$alertMessage = "";
if (isset($_GET['success'])) {
  switch ($_GET['success']) {
    case "approve":
      $alertType = "alert-success";
      $alertMessage = "Permintaan peminjaman disetujui. Buku berhasil dipinjamkan.";
      break;
    case "cancel":
      $alertType = "alert-success";
      $alertMessage = "Permintaan peminjaman dibatalkan.";
      break;
  }
} elseif (isset($_GET['error'])) {
  $alertType = "alert-error";
  switch ($_GET['error']) {
    case "approve_general":
      $alertMessage = "Gagal menyetujui permintaan. Coba ulangi.";
      break;
    default:
      $alertMessage = "Terjadi kesalahan. Silakan periksa kembali data Anda.";
      break;
  }
}

mysql_select_db($database_koneksi, $koneksi);
$query_RpendingLoans = "SELECT peminjaman.*, `user`.Username, `user`.NamaLengkap, buku.Judul FROM peminjaman INNER JOIN `user` ON peminjaman.UserID = `user`.UserID INNER JOIN buku ON peminjaman.BukuID = buku.BukuID WHERE peminjaman.StatusPeminjaman = 'diajukan' ORDER BY peminjaman.PeminjamanID ASC";
$RpendingLoans = mysql_query($query_RpendingLoans, $koneksi) or die(mysql_error());
$row_RpendingLoans = mysql_fetch_assoc($RpendingLoans);
$totalRows_RpendingLoans = mysql_num_rows($RpendingLoans);

$editFormAction = $_SERVER['PHP_SELF'];
if (!empty($currentQueryArray)) {
  $editFormAction .= '?' . htmlentities(http_build_query($currentQueryArray), ENT_QUOTES, 'UTF-8');
}

require('layout_header.php');
?>
    <main>
        <section class="content-section">
            <h2>Permintaan Peminjaman</h2>
            <p>Tinjau permintaan dengan status <strong>diajukan</strong>, kemudian setujui atau batalkan sebelum buku diambil peminjam.</p>
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
                        <th>Tanggal Pengajuan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($totalRows_RpendingLoans > 0) { ?>
                        <?php do { ?>
                        <tr>
                            <td><?php echo $row_RpendingLoans['PeminjamanID']; ?></td>
                            <td><?php echo $row_RpendingLoans['NamaLengkap'] ? $row_RpendingLoans['NamaLengkap'] : $row_RpendingLoans['Username']; ?></td>
                            <td><?php echo $row_RpendingLoans['Judul']; ?></td>
                            <td><?php echo $row_RpendingLoans['TanggalPeminjaman']; ?></td>
                            <td>
                                <form method="post" action="<?php echo $editFormAction; ?>" class="inline-form">
                                    <input type="hidden" name="PeminjamanID" value="<?php echo $row_RpendingLoans['PeminjamanID']; ?>">
                                    <input type="hidden" name="MM_update" value="approveLoan">
                                    <button type="submit" class="mini-button">Setujui</button>
                                </form>
                                <a class="mini-button danger" href="permintaan.php?cancel=<?php echo $row_RpendingLoans['PeminjamanID']; ?>" onclick="return confirm('Batalkan permintaan ini?');">Batalkan</a>
                            </td>
                        </tr>
                        <?php } while ($row_RpendingLoans = mysql_fetch_assoc($RpendingLoans)); ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="5">Belum ada permintaan baru saat ini.</td>
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
if ($totalRows_RpendingLoans > 0) {
  mysql_data_seek($RpendingLoans, 0);
}
mysql_free_result($RpendingLoans);
?>
