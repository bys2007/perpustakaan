<?php
$activeNav = 'koleksi';
$pageTitle = 'Koleksi Pribadi';
require_once('peminjam_common.php');

$currentQueryArray = $_GET;
unset($currentQueryArray['success'], $currentQueryArray['error'], $currentQueryArray['delete_koleksi']);

function redirectKoleksi($params = array()) {
  global $currentQueryArray;
  $base = 'koleksi.php';
  $query = array_merge($currentQueryArray, $params);
  $target = $base;
  if (!empty($query)) {
    $target .= '?' . http_build_query($query);
  }
  header("Location: " . $target);
  exit;
}

if ((isset($_GET['delete_koleksi'])) && ($_GET['delete_koleksi'] != "")) {
  mysql_select_db($database_koneksi, $koneksi);
  $deleteSQL = sprintf("DELETE FROM koleksipribadi WHERE KoleksiID=%s AND UserID=%s",
                       GetSQLValueString($_GET['delete_koleksi'], "int"),
                       GetSQLValueString($loggedInUserID, "int"));
  $Result1 = mysql_query($deleteSQL, $koneksi) or die(mysql_error());

  redirectKoleksi(array('success' => 'hapus_koleksi'));
}

$alertType = "";
$alertMessage = "";
if (isset($_GET['success']) && $_GET['success'] == 'hapus_koleksi') {
  $alertType = "alert-success";
  $alertMessage = "Koleksi berhasil dihapus.";
}

mysql_select_db($database_koneksi, $koneksi);
$query_Rkoleksi = sprintf("SELECT koleksipribadi.KoleksiID, koleksipribadi.BukuID, buku.Judul, buku.Penulis, buku.Penerbit FROM koleksipribadi INNER JOIN buku ON koleksipribadi.BukuID = buku.BukuID WHERE koleksipribadi.UserID = %s ORDER BY koleksipribadi.KoleksiID DESC",
                          GetSQLValueString($loggedInUserID, "int"));
$Rkoleksi = mysql_query($query_Rkoleksi, $koneksi) or die(mysql_error());
$row_Rkoleksi = mysql_fetch_assoc($Rkoleksi);
$totalRows_Rkoleksi = mysql_num_rows($Rkoleksi);

require('layout_header.php');
?>
    <main>
        <section class="content-section">
            <h2>Koleksi Pribadi</h2>
            <p>Daftar buku favorit yang Anda simpan untuk referensi cepat.</p>
            <?php if ($alertMessage != "") { ?>
                <div class="alert <?php echo $alertType; ?>"><?php echo $alertMessage; ?></div>
            <?php } ?>
        </section>

        <section class="content-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Penerbit</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($totalRows_Rkoleksi > 0) { ?>
                        <?php do { ?>
                        <tr>
                            <td><?php echo $row_Rkoleksi['Judul']; ?></td>
                            <td><?php echo $row_Rkoleksi['Penulis']; ?></td>
                            <td><?php echo $row_Rkoleksi['Penerbit']; ?></td>
                            <td>
                                <a class="mini-button danger" href="koleksi.php?delete_koleksi=<?php echo $row_Rkoleksi['KoleksiID']; ?>" onclick="return confirm('Hapus buku ini dari koleksi?');">Hapus</a>
                            </td>
                        </tr>
                        <?php } while ($row_Rkoleksi = mysql_fetch_assoc($Rkoleksi)); ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="4">Belum ada buku di koleksi pribadi Anda.</td>
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
if ($totalRows_Rkoleksi > 0) {
  mysql_data_seek($Rkoleksi, 0);
}
mysql_free_result($Rkoleksi);
?>
