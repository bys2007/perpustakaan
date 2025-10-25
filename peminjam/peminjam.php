<?php
$activeNav = 'katalog';
$pageTitle = 'Katalog Buku';
require_once('peminjam_common.php');

$currentQueryArray = $_GET;
unset($currentQueryArray['success'], $currentQueryArray['error']);

function redirectKatalog($params = array()) {
  global $currentQueryArray;
  $base = 'peminjam.php';
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "pinjam")) {
  $targetBuku = isset($_POST['BukuID']) ? $_POST['BukuID'] : "";
  if ($targetBuku == "") {
    redirectKatalog(array('error' => 'pinjam_general'));
  }
  mysql_select_db($database_koneksi, $koneksi);
  $checkSQL = sprintf("SELECT PeminjamanID FROM peminjaman WHERE UserID=%s AND BukuID=%s AND StatusPeminjaman IN ('diajukan','dipinjam')",
                       GetSQLValueString($loggedInUserID, "int"),
                       GetSQLValueString($targetBuku, "int"));
  $checkResult = mysql_query($checkSQL, $koneksi) or die(mysql_error());
  $isDuplicate = mysql_num_rows($checkResult);
  mysql_free_result($checkResult);

  if ($isDuplicate > 0) {
    redirectKatalog(array('error' => 'pinjam_duplicate'));
  }

  $insertSQL = sprintf("INSERT INTO peminjaman (UserID, BukuID, TanggalPeminjaman, StatusPeminjaman) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($loggedInUserID, "int"),
                       GetSQLValueString($targetBuku, "int"),
                       GetSQLValueString(date('Y-m-d'), "date"),
                       GetSQLValueString('diajukan', "text"));
  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($insertSQL, $koneksi) or die(mysql_error());

  redirectKatalog(array('success' => 'pinjam'));
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "koleksi")) {
  $targetBuku = isset($_POST['BukuID']) ? $_POST['BukuID'] : "";
  if ($targetBuku == "") {
    redirectKatalog(array('error' => 'general'));
  }
  mysql_select_db($database_koneksi, $koneksi);
  $checkSQL = sprintf("SELECT KoleksiID FROM koleksipribadi WHERE UserID=%s AND BukuID=%s",
                      GetSQLValueString($loggedInUserID, "int"),
                      GetSQLValueString($targetBuku, "int"));
  $checkResult = mysql_query($checkSQL, $koneksi) or die(mysql_error());
  $isDuplicate = mysql_num_rows($checkResult);
  mysql_free_result($checkResult);

  if ($isDuplicate > 0) {
    redirectKatalog(array('error' => 'koleksi_duplicate'));
  }

  $insertSQL = sprintf("INSERT INTO koleksipribadi (UserID, BukuID) VALUES (%s, %s)",
                       GetSQLValueString($loggedInUserID, "int"),
                       GetSQLValueString($targetBuku, "int"));
  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($insertSQL, $koneksi) or die(mysql_error());

  redirectKatalog(array('success' => 'koleksi'));
}

$alertType = "";
$alertMessage = "";
if (isset($_GET['success'])) {
  switch ($_GET['success']) {
    case "pinjam":
      $alertType = "alert-success";
      $alertMessage = "Permintaan peminjaman berhasil diajukan. Petugas akan memproses sebelum buku dapat dipinjam.";
      break;
    case "koleksi":
      $alertType = "alert-success";
      $alertMessage = "Buku ditambahkan ke koleksi pribadi Anda.";
      break;
  }
} elseif (isset($_GET['error'])) {
  $alertType = "alert-error";
  switch ($_GET['error']) {
    case "pinjam_duplicate":
      $alertMessage = "Anda masih memiliki peminjaman aktif untuk buku ini.";
      break;
    case "pinjam_general":
      $alertMessage = "Gagal mencatat peminjaman. Silakan coba lagi.";
      break;
    case "koleksi_duplicate":
      $alertMessage = "Buku ini sudah ada di koleksi Anda.";
      break;
    default:
      $alertMessage = "Terjadi kesalahan. Silakan coba kembali.";
      break;
  }
}

$selectedKategori = isset($_GET['kategori']) ? $_GET['kategori'] : "";
$keyword = isset($_GET['q']) ? trim($_GET['q']) : "";

mysql_select_db($database_koneksi, $koneksi);
$query_Rkategori = "SELECT * FROM kategoribuku ORDER BY NamaKategori ASC";
$Rkategori = mysql_query($query_Rkategori, $koneksi) or die(mysql_error());
$row_Rkategori = mysql_fetch_assoc($Rkategori);
$totalRows_Rkategori = mysql_num_rows($Rkategori);

$bookFilters = array();
if ($selectedKategori != "") {
  $bookFilters[] = sprintf("buku.KategoriID = %s", GetSQLValueString($selectedKategori, "int"));
}
if ($keyword != "") {
  $likeKeyword = "%" . $keyword . "%";
  $bookFilters[] = sprintf("(buku.Judul LIKE %s OR buku.Penulis LIKE %s OR buku.Penerbit LIKE %s)",
                           GetSQLValueString($likeKeyword, "text"),
                           GetSQLValueString($likeKeyword, "text"),
                           GetSQLValueString($likeKeyword, "text"));
}
$bookWhereClause = "";
if (!empty($bookFilters)) {
  $bookWhereClause = " WHERE " . implode(" AND ", $bookFilters);
}

mysql_select_db($database_koneksi, $koneksi);
$query_Rbuku = "SELECT buku.*, kategoribuku.NamaKategori FROM buku LEFT JOIN kategoribuku ON buku.KategoriID = kategoribuku.KategoriID" . $bookWhereClause . " ORDER BY buku.Judul ASC";
$Rbuku = mysql_query($query_Rbuku, $koneksi) or die(mysql_error());
$row_Rbuku = mysql_fetch_assoc($Rbuku);
$totalRows_Rbuku = mysql_num_rows($Rbuku);

mysql_select_db($database_koneksi, $koneksi);
$query_CountKoleksi = sprintf("SELECT COUNT(*) AS Total FROM koleksipribadi WHERE UserID = %s",
                              GetSQLValueString($loggedInUserID, "int"));
$CountKoleksi = mysql_query($query_CountKoleksi, $koneksi) or die(mysql_error());
$row_CountKoleksi = mysql_fetch_assoc($CountKoleksi);
$totalKoleksi = $row_CountKoleksi ? $row_CountKoleksi['Total'] : 0;

mysql_select_db($database_koneksi, $koneksi);
$query_CountUlasan = sprintf("SELECT COUNT(*) AS Total FROM ulasanbuku WHERE UserID = %s",
                             GetSQLValueString($loggedInUserID, "int"));
$CountUlasan = mysql_query($query_CountUlasan, $koneksi) or die(mysql_error());
$row_CountUlasan = mysql_fetch_assoc($CountUlasan);
$totalUlasan = $row_CountUlasan ? $row_CountUlasan['Total'] : 0;

mysql_select_db($database_koneksi, $koneksi);
$query_RpinjamanAktif = sprintf("SELECT COUNT(*) AS TotalAktif FROM peminjaman WHERE UserID=%s AND StatusPeminjaman IN ('diajukan','dipinjam')",
                                GetSQLValueString($loggedInUserID, "int"));
$RpinjamanAktif = mysql_query($query_RpinjamanAktif, $koneksi) or die(mysql_error());
$row_RpinjamanAktif = mysql_fetch_assoc($RpinjamanAktif);
$totalPinjamanAktif = $row_RpinjamanAktif ? $row_RpinjamanAktif['TotalAktif'] : 0;

require('layout_header.php');
?>
    <main>
        <section id="dashboard" class="content-section">
            <h2>Selamat datang, <?php echo $row_Ruser['NamaLengkap'] ? $row_Ruser['NamaLengkap'] : $row_Ruser['Username']; ?></h2>
            <p>Kelola peminjaman, koleksi pribadi, dan ulasan buku favorit Anda.</p>
            <?php if ($alertMessage != "") { ?>
                <div class="alert <?php echo $alertType; ?>"><?php echo $alertMessage; ?></div>
            <?php } ?>
        </section>

        <section class="content-section card-container">
            <div class="card">
                <h3>Peminjaman Aktif</h3>
                <p><?php echo $totalPinjamanAktif; ?> buku</p>
            </div>
            <div class="card">
                <h3>Koleksi Pribadi</h3>
                <p><?php echo $totalKoleksi; ?> judul</p>
            </div>
            <div class="card">
                <h3>Ulasan Saya</h3>
                <p><?php echo $totalUlasan; ?> ulasan</p>
            </div>
        </section>

        <section id="katalog" class="content-section">
            <h3>Jelajahi Katalog Buku</h3>
            <form method="get" action="peminjam.php" class="form-buku filter-form">
              <table>
                <tr>
                  <td align="left">Cari Judul / Penulis</td>
                  <td><input type="text" name="q" value="<?php echo htmlentities($keyword, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Masukkan kata kunci"></td>
                  <td align="left">Kategori</td>
                  <td>
                    <select name="kategori">
                      <option value="">Semua kategori</option>
                      <?php if ($totalRows_Rkategori > 0) { ?>
                        <?php do { ?>
                          <option value="<?php echo $row_Rkategori['KategoriID']; ?>" <?php if ($selectedKategori != "" && $selectedKategori == $row_Rkategori['KategoriID']) { echo "selected"; } ?>><?php echo $row_Rkategori['NamaKategori']; ?></option>
                        <?php } while ($row_Rkategori = mysql_fetch_assoc($Rkategori)); ?>
                        <?php mysql_data_seek($Rkategori, 0); $row_Rkategori = mysql_fetch_assoc($Rkategori); ?>
                      <?php } ?>
                    </select>
                  </td>
                  <td align="center">
                    <button type="submit" class="input-button">Terapkan Filter</button>
                  </td>
                </tr>
              </table>
            </form>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Penulis</th>
                        <th>Penerbit</th>
                        <th>Tahun</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($totalRows_Rbuku > 0) { ?>
                        <?php do { ?>
                        <tr>
                            <td><?php echo $row_Rbuku['Judul']; ?></td>
                            <td><?php echo $row_Rbuku['NamaKategori'] ? $row_Rbuku['NamaKategori'] : 'Tanpa Kategori'; ?></td>
                            <td><?php echo $row_Rbuku['Penulis']; ?></td>
                            <td><?php echo $row_Rbuku['Penerbit']; ?></td>
                            <td><?php echo $row_Rbuku['TahunTerbit']; ?></td>
                            <td class="action-links">
                                <form method="post" action="<?php echo $editFormAction; ?>" class="inline-form">
                                    <input type="hidden" name="BukuID" value="<?php echo $row_Rbuku['BukuID']; ?>">
                                    <input type="hidden" name="MM_insert" value="pinjam">
                                    <button type="submit" class="mini-button">Pinjam</button>
                                </form>
                                <form method="post" action="<?php echo $editFormAction; ?>" class="inline-form">
                                    <input type="hidden" name="BukuID" value="<?php echo $row_Rbuku['BukuID']; ?>">
                                    <input type="hidden" name="MM_insert" value="koleksi">
                                    <button type="submit" class="mini-button alt">Tambah Koleksi</button>
                                </form>
                            </td>
                        </tr>
                        <?php } while ($row_Rbuku = mysql_fetch_assoc($Rbuku)); ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6">Tidak ada buku yang cocok dengan filter.</td>
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
mysql_free_result($Rkategori);
mysql_free_result($Rbuku);
mysql_free_result($CountKoleksi);
mysql_free_result($CountUlasan);
mysql_free_result($RpinjamanAktif);
?>
