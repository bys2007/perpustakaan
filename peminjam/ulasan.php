<?php
$activeNav = 'ulasan';
$pageTitle = 'Ulasan Buku';
require_once('peminjam_common.php');

$currentQueryArray = $_GET;
unset($currentQueryArray['success'], $currentQueryArray['error'], $currentQueryArray['delete_ulasan']);

function redirectUlasan($params = array()) {
  global $currentQueryArray;
  $base = 'ulasan.php';
  $query = array_merge($currentQueryArray, $params);
  $target = $base;
  if (!empty($query)) {
    $target .= '?' . http_build_query($query);
  }
  header("Location: " . $target);
  exit;
}

$editFormAction = $_SERVER['PHP_SELF'];
if (!empty($currentQueryArray)) {
  $editFormAction .= '?' . htmlentities(http_build_query($currentQueryArray), ENT_QUOTES, 'UTF-8');
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "ulasan")) {
  $targetBuku = isset($_POST['BukuID']) ? $_POST['BukuID'] : "";
  $ulasanText = isset($_POST['Ulasan']) ? trim($_POST['Ulasan']) : "";
  $ratingInput = isset($_POST['Rating']) ? intval($_POST['Rating']) : 0;

  if ($targetBuku == "" || $ulasanText == "" || $ratingInput < 1 || $ratingInput > 5) {
    redirectUlasan(array('error' => 'ulasan_incomplete'));
  }

  mysql_select_db($database_koneksi, $koneksi);
  $borrowCheckSQL = sprintf("SELECT PeminjamanID FROM peminjaman WHERE UserID=%s AND BukuID=%s AND StatusPeminjaman IN ('dipinjam','dikembalikan') LIMIT 1",
                             GetSQLValueString($loggedInUserID, "int"),
                             GetSQLValueString($targetBuku, "int"));
  $borrowCheckResult = mysql_query($borrowCheckSQL, $koneksi) or die(mysql_error());
  $hasBorrowed = mysql_num_rows($borrowCheckResult) > 0;
  mysql_free_result($borrowCheckResult);

  if (!$hasBorrowed) {
    redirectUlasan(array('error' => 'ulasan_not_allowed'));
  }

  $checkSQL = sprintf("SELECT UlasanID FROM ulasanbuku WHERE UserID=%s AND BukuID=%s",
                      GetSQLValueString($loggedInUserID, "int"),
                      GetSQLValueString($targetBuku, "int"));
  $checkResult = mysql_query($checkSQL, $koneksi) or die(mysql_error());
  $hasExisting = mysql_num_rows($checkResult) > 0;
  $existingReview = mysql_fetch_assoc($checkResult);
  $existingReviewID = $hasExisting ? $existingReview['UlasanID'] : null;
  mysql_free_result($checkResult);

  if ($hasExisting) {
    $updateSQL = sprintf("UPDATE ulasanbuku SET Ulasan=%s, Rating=%s WHERE UlasanID=%s AND UserID=%s",
                         GetSQLValueString($ulasanText, "text"),
                         GetSQLValueString($ratingInput, "int"),
                         GetSQLValueString($existingReviewID, "int"),
                         GetSQLValueString($loggedInUserID, "int"));
    mysql_select_db($database_koneksi, $koneksi);
    $Result1 = mysql_query($updateSQL, $koneksi) or die(mysql_error());
    redirectUlasan(array('success' => 'ulasan_update'));
  } else {
    $insertSQL = sprintf("INSERT INTO ulasanbuku (UserID, BukuID, Ulasan, Rating) VALUES (%s, %s, %s, %s)",
                         GetSQLValueString($loggedInUserID, "int"),
                         GetSQLValueString($targetBuku, "int"),
                         GetSQLValueString($ulasanText, "text"),
                         GetSQLValueString($ratingInput, "int"));
    mysql_select_db($database_koneksi, $koneksi);
    $Result1 = mysql_query($insertSQL, $koneksi) or die(mysql_error());
    redirectUlasan(array('success' => 'ulasan_insert'));
  }
}

if ((isset($_GET['delete_ulasan'])) && ($_GET['delete_ulasan'] != "")) {
  mysql_select_db($database_koneksi, $koneksi);
  $deleteSQL = sprintf("DELETE FROM ulasanbuku WHERE UlasanID=%s AND UserID=%s",
                       GetSQLValueString($_GET['delete_ulasan'], "int"),
                       GetSQLValueString($loggedInUserID, "int"));
  $Result1 = mysql_query($deleteSQL, $koneksi) or die(mysql_error());

  redirectUlasan(array('success' => 'hapus_ulasan'));
}

$alertType = "";
$alertMessage = "";
if (isset($_GET['success'])) {
  switch ($_GET['success']) {
    case "ulasan_insert":
      $alertType = "alert-success";
      $alertMessage = "Terima kasih! Ulasan Anda telah disimpan.";
      break;
    case "ulasan_update":
      $alertType = "alert-success";
      $alertMessage = "Ulasan Anda berhasil diperbarui.";
      break;
    case "hapus_ulasan":
      $alertType = "alert-success";
      $alertMessage = "Ulasan berhasil dihapus.";
      break;
  }
} elseif (isset($_GET['error'])) {
  $alertType = "alert-error";
  switch ($_GET['error']) {
    case "ulasan_incomplete":
      $alertMessage = "Lengkapi pilihan buku, ulasan, dan rating sebelum menyimpan.";
      break;
    case "ulasan_not_allowed":
      $alertMessage = "Anda hanya dapat mengulas buku yang sedang atau pernah Anda pinjam.";
      break;
    default:
      $alertMessage = "Terjadi kesalahan. Silakan coba kembali.";
      break;
  }
}

mysql_select_db($database_koneksi, $koneksi);
$query_RbooksSelect = sprintf("SELECT DISTINCT buku.BukuID, buku.Judul FROM peminjaman INNER JOIN buku ON peminjaman.BukuID = buku.BukuID WHERE peminjaman.UserID = %s AND peminjaman.StatusPeminjaman IN ('dipinjam','dikembalikan') ORDER BY buku.Judul ASC",
                              GetSQLValueString($loggedInUserID, "int"));
$RbooksSelect = mysql_query($query_RbooksSelect, $koneksi) or die(mysql_error());
$row_RbooksSelect = mysql_fetch_assoc($RbooksSelect);
$totalRows_RbooksSelect = mysql_num_rows($RbooksSelect);
$hasReviewableBooks = $totalRows_RbooksSelect > 0;

mysql_select_db($database_koneksi, $koneksi);
$query_Rulasan = sprintf("SELECT ulasanbuku.*, buku.Judul FROM ulasanbuku INNER JOIN buku ON ulasanbuku.BukuID = buku.BukuID WHERE ulasanbuku.UserID = %s ORDER BY ulasanbuku.UlasanID DESC",
                         GetSQLValueString($loggedInUserID, "int"));
$Rulasan = mysql_query($query_Rulasan, $koneksi) or die(mysql_error());
$row_Rulasan = mysql_fetch_assoc($Rulasan);
$totalRows_Rulasan = mysql_num_rows($Rulasan);

require('layout_header.php');
?>
    <main>
        <section class="content-section">
            <h2>Ulasan Buku</h2>
            <p>Bagikan pengalaman membaca Anda untuk membantu peminjam lainnya.</p>
            <?php if ($alertMessage != "") { ?>
                <div class="alert <?php echo $alertType; ?>"><?php echo $alertMessage; ?></div>
            <?php } ?>
        </section>

        <section class="content-section">
            <form method="post" action="<?php echo $editFormAction; ?>" class="form-buku" style="margin-top:1.5rem;">
              <table>
                <tr>
                  <td align="left">Judul Buku</td>
                  <td>
                    <select name="BukuID" required <?php if (!$hasReviewableBooks) { echo 'disabled'; } ?>>
                      <option value=""><?php echo $hasReviewableBooks ? 'Pilih buku' : 'Belum ada buku yang dapat Anda ulas'; ?></option>
                      <?php if ($hasReviewableBooks) { ?>
                        <?php do { ?>
                          <option value="<?php echo $row_RbooksSelect['BukuID']; ?>"><?php echo $row_RbooksSelect['Judul']; ?></option>
                        <?php } while ($row_RbooksSelect = mysql_fetch_assoc($RbooksSelect)); ?>
                        <?php mysql_data_seek($RbooksSelect, 0); $row_RbooksSelect = mysql_fetch_assoc($RbooksSelect); ?>
                      <?php } ?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td align="left">Ulasan</td>
                  <td><textarea name="Ulasan" placeholder="Tulis ulasan singkat Anda" required></textarea></td>
                </tr>
                <tr>
                  <td align="left">Rating</td>
                  <td>
                    <select name="Rating" required>
                      <option value="">Pilih rating</option>
                      <option value="5">5 - Luar biasa</option>
                      <option value="4">4 - Bagus</option>
                      <option value="3">3 - Cukup</option>
                      <option value="2">2 - Kurang</option>
                      <option value="1">1 - Tidak suka</option>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" align="center">
                    <input type="hidden" name="MM_insert" value="ulasan">
                    <button type="submit" class="input-button" <?php if (!$hasReviewableBooks) { echo 'disabled style="opacity:0.6; cursor:not-allowed;"'; } ?>>Simpan Ulasan</button>
                  </td>
                </tr>
              </table>
            </form>
            <?php if (!$hasReviewableBooks) { ?>
              <p style="margin-top:1rem; color: var(--text-color); text-align:center;">Anda dapat menulis ulasan setelah menyelesaikan peminjaman buku.</p>
            <?php } ?>
        </section>

        <section class="content-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Ulasan</th>
                        <th>Rating</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($totalRows_Rulasan > 0) { ?>
                        <?php do { ?>
                        <tr>
                            <td><?php echo $row_Rulasan['Judul']; ?></td>
                            <td><?php echo nl2br(htmlentities($row_Rulasan['Ulasan'], ENT_QUOTES, 'UTF-8')); ?></td>
                            <td><?php echo $row_Rulasan['Rating']; ?>/5</td>
                            <td>
                                <a class="mini-button danger" href="ulasan.php?delete_ulasan=<?php echo $row_Rulasan['UlasanID']; ?>" onclick="return confirm('Hapus ulasan ini?');">Hapus</a>
                            </td>
                        </tr>
                        <?php } while ($row_Rulasan = mysql_fetch_assoc($Rulasan)); ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="4">Belum ada ulasan yang Anda tulis.</td>
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
if ($totalRows_RbooksSelect > 0) {
  mysql_data_seek($RbooksSelect, 0);
}
mysql_free_result($RbooksSelect);
if ($totalRows_Rulasan > 0) {
  mysql_data_seek($Rulasan, 0);
}
mysql_free_result($Rulasan);
?>
