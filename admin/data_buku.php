<?php require_once('../Connections/koneksi.php'); ?>
<?php
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

// ** Logout the current user. **
$logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")){
  $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
  //to fully log out a visitor we need to clear the session varialbles
  $_SESSION['MM_Username'] = NULL;
  $_SESSION['MM_UserGroup'] = NULL;
  $_SESSION['PrevUrl'] = NULL;
  unset($_SESSION['MM_Username']);
  unset($_SESSION['MM_UserGroup']);
  unset($_SESSION['PrevUrl']);
	
  $logoutGoTo = "../index.php";
  if ($logoutGoTo) {
    header("Location: $logoutGoTo");
    exit;
  }
}
?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "administrator";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../petugas/petugas.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}
?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE buku SET Judul=%s, KategoriID=%s, Penulis=%s, Penerbit=%s, TahunTerbit=%s WHERE BukuID=%s",
                       GetSQLValueString($_POST['Judul'], "text"),
                       GetSQLValueString($_POST['KategoriID'], "int"),
                       GetSQLValueString($_POST['Penulis'], "text"),
                       GetSQLValueString($_POST['Penerbit'], "text"),
                       GetSQLValueString($_POST['TahunTerbit'], "int"),
                       GetSQLValueString($_POST['BukuID'], "int"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($updateSQL, $koneksi) or die(mysql_error());

  $updateGoTo = "data_buku.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {
  $insertSQL = sprintf("INSERT INTO buku (BukuID, Judul, KategoriID, Penulis, Penerbit, TahunTerbit) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['BukuID'], "int"),
                       GetSQLValueString($_POST['Judul'], "text"),
                       GetSQLValueString($_POST['KategoriID'], "int"),
                       GetSQLValueString($_POST['Penulis'], "text"),
                       GetSQLValueString($_POST['Penerbit'], "text"),
                       GetSQLValueString($_POST['TahunTerbit'], "int"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($insertSQL, $koneksi) or die(mysql_error());

  $insertGoTo = "data_buku.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

$colname_Ruser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_Ruser = $_SESSION['MM_Username'];
}
mysql_select_db($database_koneksi, $koneksi);
$query_Ruser = sprintf("SELECT * FROM `user` WHERE Username = %s", GetSQLValueString($colname_Ruser, "text"));
$Ruser = mysql_query($query_Ruser, $koneksi) or die(mysql_error());
$row_Ruser = mysql_fetch_assoc($Ruser);
$totalRows_Ruser = mysql_num_rows($Ruser);

mysql_select_db($database_koneksi, $koneksi);
$query_Rbuku = "SELECT buku.*, kategoribuku.NamaKategori FROM buku LEFT JOIN kategoribuku ON buku.KategoriID = kategoribuku.KategoriID ORDER BY buku.BukuID DESC";
$Rbuku = mysql_query($query_Rbuku, $koneksi) or die(mysql_error());
$row_Rbuku = mysql_fetch_assoc($Rbuku);
$totalRows_Rbuku = mysql_num_rows($Rbuku);

mysql_select_db($database_koneksi, $koneksi);
$query_Rkategori = "SELECT * FROM kategoribuku";
$Rkategori = mysql_query($query_Rkategori, $koneksi) or die(mysql_error());
$row_Rkategori = mysql_fetch_assoc($Rkategori);
$totalRows_Rkategori = mysql_num_rows($Rkategori);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gaegu:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="cursor-dot"></div>
    <div class="cursor-outline"></div>

    <main>
        <section class="content-section">
            <h2>Kelola Koleksi Buku</h2>
            <p>Tambah judul baru, kelola kategori, dan pantau seluruh koleksi perpustakaan di daftar berikut.</p>
        </section>

        <section id="tambah-buku" class="content-section form-buku">
          <form method="post" name="form2" action="<?php echo $editFormAction; ?>">
            <table>
              <tr valign="baseline">
                <td align="left">Judul</td>
                <td><input type="text" name="Judul" value="" size="32" required></td>
              </tr>
              <tr valign="baseline">
                <td align="left">Kategori</td>
                <td>
                  <select name="KategoriID" required>
                    <option value="">--Pilih kategori--</option>
                    <?php if ($totalRows_Rkategori > 0) { ?>
                      <?php do { ?>
                        <option value="<?php echo $row_Rkategori['KategoriID']; ?>"><?php echo $row_Rkategori['NamaKategori']; ?></option>
                      <?php } while ($row_Rkategori = mysql_fetch_assoc($Rkategori)); ?>
                      <?php mysql_data_seek($Rkategori, 0); $row_Rkategori = mysql_fetch_assoc($Rkategori); ?>
                    <?php } else { ?>
                      <option value="">Belum ada kategori</option>
                    <?php } ?>
                  </select>
                </td>
              </tr>
              <tr valign="baseline">
                <td align="left">Penulis</td>
                <td><input type="text" name="Penulis" value="" size="32" required></td>
              </tr>
              <tr valign="baseline">
                <td align="left">Penerbit</td>
                <td><input type="text" name="Penerbit" value="" size="32" required></td>
              </tr>
              <tr valign="baseline">
                <td align="left">Tahun Terbit</td>
                <td><input type="text" name="TahunTerbit" value="" size="32" required></td>
              </tr>
              <tr valign="baseline">
                <td colspan="2" align="center">
                  <button type="submit" class="input-button">Tambah Buku</button>
                </td>
              </tr>
            </table>
            <input type="hidden" name="BukuID" value="">
            <input type="hidden" name="MM_insert" value="form2">
          </form>
        </section>

        <section id="daftar-buku" class="content-section">
            <h3>Daftar Buku</h3>
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Judul</th>
                  <th>Kategori</th>
                  <th>Penulis</th>
                  <th>Penerbit</th>
                  <th>Tahun Terbit</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($totalRows_Rbuku > 0) { ?>
                  <?php do { ?>
                  <tr>
                    <td><?php echo $row_Rbuku['BukuID']; ?></td>
                    <td><?php echo $row_Rbuku['Judul']; ?></td>
                    <td><?php echo $row_Rbuku['NamaKategori'] ? $row_Rbuku['NamaKategori'] : 'Tanpa Kategori'; ?></td>
                    <td><?php echo $row_Rbuku['Penulis']; ?></td>
                    <td><?php echo $row_Rbuku['Penerbit']; ?></td>
                    <td><?php echo $row_Rbuku['TahunTerbit']; ?></td>
                    <td class="action-links">
                      <a href="#" class="edit-btn book-edit-btn" 
                         data-id="<?php echo $row_Rbuku['BukuID']; ?>"
                         data-judul="<?php echo htmlentities($row_Rbuku['Judul'], ENT_QUOTES, 'UTF-8'); ?>"
                         data-penulis="<?php echo htmlentities($row_Rbuku['Penulis'], ENT_QUOTES, 'UTF-8'); ?>"
                         data-penerbit="<?php echo htmlentities($row_Rbuku['Penerbit'], ENT_QUOTES, 'UTF-8'); ?>"
                         data-tahun="<?php echo $row_Rbuku['TahunTerbit']; ?>"
                         data-kategori="<?php echo $row_Rbuku['KategoriID']; ?>">Edit</a>
                      <a href="delete_buku.php?BukuID=<?php echo $row_Rbuku['BukuID']; ?>" class="delete-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?');">Delete</a>
                    </td>
                  </tr>
                  <?php } while ($row_Rbuku = mysql_fetch_assoc($Rbuku)); ?>
                <?php } else { ?>
                  <tr>
                    <td colspan="7">Belum ada data buku.</td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
        </section>
    </main>

    <nav class="bottom-nav">
        <a href="admin.php">Dashboard</a>
        <a href="data_user.php">User</a>
        <a href="data_buku.php" class="active">Buku</a>
        <a href="data_kategori.php">Kategori</a>
        <a href="data_peminjaman.php">Peminjaman</a>
        <a href="laporan.php">Laporan</a>
        <a href="<?php echo $logoutAction ?>">Logout</a>
    </nav>
    
    <footer class="footer">
        <p>Perpustakaan BYS</p>
        <p>&copy; 2025 Perpustakaan BYS. All rights reserved.</p>
    </footer>

    <div id="editModal" class="modal" style="display:none;">
      <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Edit Buku</h2>
        <form method="post" name="form1" action="<?php echo $editFormAction; ?>">
          <table align="center">
            <tr valign="baseline">
              <td nowrap align="right">Judul:</td>
              <td><input type="text" name="Judul" id="editJudul" value="" size="32"></td>
            </tr>
            <tr valign="baseline">
              <td nowrap align="right">Kategori:</td>
              <td>
                <select name="KategoriID" id="editKategori">
                  <?php if ($totalRows_Rkategori > 0) { ?>
                    <?php do { ?>
                      <option value="<?php echo $row_Rkategori['KategoriID']; ?>"><?php echo $row_Rkategori['NamaKategori']; ?></option>
                    <?php } while ($row_Rkategori = mysql_fetch_assoc($Rkategori)); ?>
                    <?php mysql_data_seek($Rkategori, 0); $row_Rkategori = mysql_fetch_assoc($Rkategori); ?>
                  <?php } ?>
                </select>
              </td>
            </tr>
            <tr valign="baseline">
              <td nowrap align="right">Penulis:</td>
              <td><input type="text" name="Penulis" id="editPenulis" value="" size="32"></td>
            </tr>
            <tr valign="baseline">
              <td nowrap align="right">Penerbit:</td>
              <td><input type="text" name="Penerbit" id="editPenerbit" value="" size="32"></td>
            </tr>
            <tr valign="baseline">
              <td nowrap align="right">Tahun Terbit:</td>
              <td><input type="text" name="TahunTerbit" id="editTahun" value="" size="32"></td>
            </tr>
            <tr valign="baseline">
              <td nowrap align="right">&nbsp;</td>
              <td><input type="submit" class="input-button" value="Update Data"></td>
            </tr>
          </table>
          <input type="hidden" name="MM_update" value="form1">
          <input type="hidden" name="BukuID" id="editBukuID" value="">
        </form>
      </div>
    </div>

    <script src="../assets/script.js"></script>
</body>
</html>
<?php
mysql_free_result($Ruser);

mysql_free_result($Rbuku);

mysql_free_result($Rkategori);
?>
