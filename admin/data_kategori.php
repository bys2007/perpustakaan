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

function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  $isValid = False; 
  if (!empty($UserName)) { 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "formTambah")) {
  $insertSQL = sprintf("INSERT INTO kategoribuku (KategoriID, NamaKategori) VALUES (%s, %s)",
                       GetSQLValueString($_POST['KategoriID'], "int"),
                       GetSQLValueString($_POST['NamaKategori'], "text"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($insertSQL, $koneksi) or die(mysql_error());

  $insertGoTo = "data_kategori.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "formEdit")) {
  $updateSQL = sprintf("UPDATE kategoribuku SET NamaKategori=%s WHERE KategoriID=%s",
                       GetSQLValueString($_POST['NamaKategori'], "text"),
                       GetSQLValueString($_POST['KategoriID'], "int"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($updateSQL, $koneksi) or die(mysql_error());

  $updateGoTo = "data_kategori.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
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
$query_Rkategori = "SELECT * FROM kategoribuku ORDER BY NamaKategori ASC";
$Rkategori = mysql_query($query_Rkategori, $koneksi) or die(mysql_error());
$row_Rkategori = mysql_fetch_assoc($Rkategori);
$totalRows_Rkategori = mysql_num_rows($Rkategori);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori</title>
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
            <h2>Kelola Kategori Buku</h2>
            <p>Kategori memudahkan pencarian buku oleh peminjam. Tambahkan, ubah, atau hapus kategori sesuai kebutuhan koleksi.</p>
        </section>

        <section class="content-section form-buku">
            <form method="post" name="formTambah" action="<?php echo $editFormAction; ?>">
                <table>
                    <tr valign="baseline">
                        <td align="left">Nama Kategori</td>
                        <td><input type="text" name="NamaKategori" value="" size="32" required></td>
                    </tr>
                    <tr valign="baseline">
                        <td colspan="2" align="center">
                            <button type="submit" class="input-button">Tambah Kategori</button>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="KategoriID" value="">
                <input type="hidden" name="MM_insert" value="formTambah">
            </form>
        </section>

        <section class="content-section">
            <h3>Daftar Kategori</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kategori</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($totalRows_Rkategori > 0) { ?>
                    <?php do { ?>
                        <tr>
                            <td><?php echo $row_Rkategori['KategoriID']; ?></td>
                            <td><?php echo $row_Rkategori['NamaKategori']; ?></td>
                            <td class="action-links">
                                <a href="#" class="edit-btn kategori-edit-btn"
                                   data-id="<?php echo $row_Rkategori['KategoriID']; ?>"
                                   data-nama="<?php echo htmlentities($row_Rkategori['NamaKategori'], ENT_QUOTES, 'UTF-8'); ?>">Edit</a>
                                <a href="delete_kategori.php?KategoriID=<?php echo $row_Rkategori['KategoriID']; ?>" class="delete-btn" onclick="return confirm('Hapus kategori ini?');">Delete</a>
                            </td>
                        </tr>
                    <?php } while ($row_Rkategori = mysql_fetch_assoc($Rkategori)); ?>
                    <?php mysql_data_seek($Rkategori, 0); $row_Rkategori = mysql_fetch_assoc($Rkategori); ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="3">Belum ada kategori tercatat.</td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </section>
    </main>

    <nav class="bottom-nav">
        <a href="admin.php">Dashboard</a>
        <a href="data_user.php">User</a>
        <a href="data_buku.php">Buku</a>
        <a href="data_kategori.php" class="active">Kategori</a>
        <a href="data_peminjaman.php">Peminjaman</a>
        <a href="laporan.php">Laporan</a>
        <a href="<?php echo $logoutAction; ?>">Logout</a>
    </nav>

    <footer class="footer">
        <p>Perpustakaan BYS</p>
        <p>&copy; 2025 Perpustakaan BYS. All rights reserved.</p>
    </footer>

    <div id="kategoriEditModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>Edit Kategori</h2>
            <form method="post" name="formEdit" action="<?php echo $editFormAction; ?>">
                <table align="center">
                    <tr valign="baseline">
                        <td align="right">Nama Kategori:</td>
                        <td><input type="text" name="NamaKategori" id="kategoriEditNama" size="32" required></td>
                    </tr>
                    <tr valign="baseline">
                        <td align="right">&nbsp;</td>
                        <td><input type="submit" class="input-button" value="Simpan Perubahan"></td>
                    </tr>
                </table>
                <input type="hidden" name="KategoriID" id="kategoriEditID" value="">
                <input type="hidden" name="MM_update" value="formEdit">
            </form>
        </div>
    </div>

    <script src="../assets/script.js"></script>
</body>
</html>
<?php
mysql_free_result($Ruser);
if ($totalRows_Rkategori > 0) {
  mysql_data_seek($Rkategori, 0);
}
mysql_free_result($Rkategori);
?>
