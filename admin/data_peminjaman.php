<?php require_once('../Connections/koneksi.php'); ?>
<?php
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "formPinjam")) {
  $status = isset($_POST['StatusPeminjaman']) && $_POST['StatusPeminjaman'] != "" ? $_POST['StatusPeminjaman'] : "diajukan";
  $status = strtolower($status);
  $insertSQL = sprintf("INSERT INTO peminjaman (PeminjamanID, UserID, BukuID, TanggalPeminjaman, TanggalPengembalian, StatusPeminjaman) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['PeminjamanID'], "int"),
                       GetSQLValueString($_POST['UserID'], "int"),
                       GetSQLValueString($_POST['BukuID'], "int"),
                       GetSQLValueString($_POST['TanggalPeminjaman'], "date"),
                       GetSQLValueString($_POST['TanggalPengembalian'], "date"),
                       GetSQLValueString($status, "text"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($insertSQL, $koneksi) or die(mysql_error());

  $insertGoTo = "data_peminjaman.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "formUpdatePinjam")) {
  $statusUpdate = isset($_POST['StatusPeminjaman']) ? strtolower($_POST['StatusPeminjaman']) : "";
  $updateSQL = sprintf("UPDATE peminjaman SET TanggalPengembalian=%s, StatusPeminjaman=%s WHERE PeminjamanID=%s",
                       GetSQLValueString($_POST['TanggalPengembalian'], "date"),
                       GetSQLValueString($statusUpdate, "text"),
                       GetSQLValueString($_POST['PeminjamanID'], "int"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($updateSQL, $koneksi) or die(mysql_error());

  $updateGoTo = "data_peminjaman.php";
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
$query_Rpeminjam = "SELECT UserID, Username FROM `user` WHERE Level = 'peminjam' ORDER BY Username ASC";
$Rpeminjam = mysql_query($query_Rpeminjam, $koneksi) or die(mysql_error());
$row_Rpeminjam = mysql_fetch_assoc($Rpeminjam);
$totalRows_Rpeminjam = mysql_num_rows($Rpeminjam);

mysql_select_db($database_koneksi, $koneksi);
$query_Rbuku = "SELECT BukuID, Judul FROM buku ORDER BY Judul ASC";
$Rbuku = mysql_query($query_Rbuku, $koneksi) or die(mysql_error());
$row_Rbuku = mysql_fetch_assoc($Rbuku);
$totalRows_Rbuku = mysql_num_rows($Rbuku);

mysql_select_db($database_koneksi, $koneksi);
$query_Rpeminjaman = "SELECT peminjaman.*, `user`.Username, buku.Judul FROM peminjaman INNER JOIN `user` ON peminjaman.UserID = `user`.UserID INNER JOIN buku ON peminjaman.BukuID = buku.BukuID ORDER BY peminjaman.PeminjamanID DESC";
$Rpeminjaman = mysql_query($query_Rpeminjaman, $koneksi) or die(mysql_error());
$row_Rpeminjaman = mysql_fetch_assoc($Rpeminjaman);
$totalRows_Rpeminjaman = mysql_num_rows($Rpeminjaman);

mysql_select_db($database_koneksi, $koneksi);
$query_Rkoleksi = "SELECT koleksipribadi.KoleksiID, `user`.Username, buku.Judul FROM koleksipribadi INNER JOIN `user` ON koleksipribadi.UserID = `user`.UserID INNER JOIN buku ON koleksipribadi.BukuID = buku.BukuID ORDER BY koleksipribadi.KoleksiID DESC";
$Rkoleksi = mysql_query($query_Rkoleksi, $koneksi) or die(mysql_error());
$row_Rkoleksi = mysql_fetch_assoc($Rkoleksi);
$totalRows_Rkoleksi = mysql_num_rows($Rkoleksi);

mysql_select_db($database_koneksi, $koneksi);
$query_Rulasan = "SELECT ulasanbuku.*, `user`.Username, buku.Judul FROM ulasanbuku INNER JOIN `user` ON ulasanbuku.UserID = `user`.UserID INNER JOIN buku ON ulasanbuku.BukuID = buku.BukuID ORDER BY ulasanbuku.UlasanID DESC";
$Rulasan = mysql_query($query_Rulasan, $koneksi) or die(mysql_error());
$row_Rulasan = mysql_fetch_assoc($Rulasan);
$totalRows_Rulasan = mysql_num_rows($Rulasan);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Peminjaman</title>
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
            <h2>Kelola Peminjaman & Aktivitas Anggota</h2>
            <p>Catat transaksi peminjaman, pantau pengembalian, serta kelola koleksi pribadi dan ulasan buku yang dibuat pengguna.</p>
        </section>

        <section class="content-section form-buku">
            <form method="post" name="formPinjam" action="<?php echo $editFormAction; ?>">
                <table>
                    <tr valign="baseline">
                        <td align="left">Peminjam</td>
                        <td>
                            <select name="UserID" required>
                                <option value="">--Pilih peminjam--</option>
                                <?php if ($totalRows_Rpeminjam > 0) { ?>
                                    <?php do { ?>
                                        <option value="<?php echo $row_Rpeminjam['UserID']; ?>"><?php echo $row_Rpeminjam['Username']; ?></option>
                                    <?php } while ($row_Rpeminjam = mysql_fetch_assoc($Rpeminjam)); ?>
                                    <?php mysql_data_seek($Rpeminjam, 0); $row_Rpeminjam = mysql_fetch_assoc($Rpeminjam); ?>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="baseline">
                        <td align="left">Buku</td>
                        <td>
                            <select name="BukuID" required>
                                <option value="">--Pilih buku--</option>
                                <?php if ($totalRows_Rbuku > 0) { ?>
                                    <?php do { ?>
                                        <option value="<?php echo $row_Rbuku['BukuID']; ?>"><?php echo $row_Rbuku['Judul']; ?></option>
                                    <?php } while ($row_Rbuku = mysql_fetch_assoc($Rbuku)); ?>
                                    <?php mysql_data_seek($Rbuku, 0); $row_Rbuku = mysql_fetch_assoc($Rbuku); ?>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="baseline">
                        <td align="left">Tanggal Peminjaman</td>
                        <td><input type="date" name="TanggalPeminjaman" required></td>
                    </tr>
                    <tr valign="baseline">
                        <td align="left">Tanggal Pengembalian</td>
                        <td><input type="date" name="TanggalPengembalian"></td>
                    </tr>
                    <tr valign="baseline">
                        <td align="left">Status</td>
                        <td>
                            <select name="StatusPeminjaman">
                                <option value="diajukan">Diajukan</option>
                                <option value="dipinjam">Dipinjam</option>
                                <option value="dikembalikan">Dikembalikan</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="baseline">
                        <td colspan="2" align="center">
                            <button type="submit" class="input-button">Catat Peminjaman</button>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="PeminjamanID" value="">
                <input type="hidden" name="MM_insert" value="formPinjam">
            </form>
        </section>

        <section class="content-section">
            <h3>Daftar Peminjaman</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Peminjam</th>
                        <th>Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($totalRows_Rpeminjaman > 0) { ?>
                    <?php do { ?>
                        <tr>
                            <td><?php echo $row_Rpeminjaman['PeminjamanID']; ?></td>
                            <td><?php echo $row_Rpeminjaman['Username']; ?></td>
                            <td><?php echo $row_Rpeminjaman['Judul']; ?></td>
                            <td><?php echo $row_Rpeminjaman['TanggalPeminjaman']; ?></td>
                            <td><?php echo $row_Rpeminjaman['TanggalPengembalian'] ? $row_Rpeminjaman['TanggalPengembalian'] : '-'; ?></td>
                            <td><span class="pill <?php echo strtolower($row_Rpeminjaman['StatusPeminjaman']); ?>"><?php echo ucfirst($row_Rpeminjaman['StatusPeminjaman']); ?></span></td>
                            <td class="action-links">
                                <a href="#" class="edit-btn peminjaman-edit-btn"
                                   data-id="<?php echo $row_Rpeminjaman['PeminjamanID']; ?>"
                                   data-status="<?php echo $row_Rpeminjaman['StatusPeminjaman']; ?>"
                                   data-kembali="<?php echo $row_Rpeminjaman['TanggalPengembalian']; ?>">Update</a>
                                <a href="delete_peminjaman.php?PeminjamanID=<?php echo $row_Rpeminjaman['PeminjamanID']; ?>" class="delete-btn" onclick="return confirm('Hapus transaksi ini?');">Delete</a>
                            </td>
                        </tr>
                    <?php } while ($row_Rpeminjaman = mysql_fetch_assoc($Rpeminjaman)); ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="7">Belum ada data peminjaman.</td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </section>

        <section class="content-section">
            <h3>Koleksi Pribadi Pengguna</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pengguna</th>
                        <th>Judul Buku</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($totalRows_Rkoleksi > 0) { ?>
                    <?php do { ?>
                        <tr>
                            <td><?php echo $row_Rkoleksi['KoleksiID']; ?></td>
                            <td><?php echo $row_Rkoleksi['Username']; ?></td>
                            <td><?php echo $row_Rkoleksi['Judul']; ?></td>
                            <td class="action-links">
                                <a href="delete_koleksi.php?KoleksiID=<?php echo $row_Rkoleksi['KoleksiID']; ?>" class="delete-btn" onclick="return confirm('Hapus koleksi ini?');">Delete</a>
                            </td>
                        </tr>
                    <?php } while ($row_Rkoleksi = mysql_fetch_assoc($Rkoleksi)); ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="4">Belum ada koleksi pribadi tercatat.</td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </section>

        <section class="content-section">
            <h3>Ulasan Buku</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pengguna</th>
                        <th>Buku</th>
                        <th>Ulasan</th>
                        <th>Rating</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($totalRows_Rulasan > 0) { ?>
                    <?php do { ?>
                        <tr>
                            <td><?php echo $row_Rulasan['UlasanID']; ?></td>
                            <td><?php echo $row_Rulasan['Username']; ?></td>
                            <td><?php echo $row_Rulasan['Judul']; ?></td>
                            <td><?php echo nl2br(htmlentities($row_Rulasan['Ulasan'], ENT_QUOTES, 'UTF-8')); ?></td>
                            <td><?php echo $row_Rulasan['Rating']; ?></td>
                            <td class="action-links">
                                <a href="delete_ulasan.php?UlasanID=<?php echo $row_Rulasan['UlasanID']; ?>" class="delete-btn" onclick="return confirm('Hapus ulasan ini?');">Delete</a>
                            </td>
                        </tr>
                    <?php } while ($row_Rulasan = mysql_fetch_assoc($Rulasan)); ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="6">Belum ada ulasan buku.</td>
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
        <a href="data_kategori.php">Kategori</a>
        <a href="data_peminjaman.php" class="active">Peminjaman</a>
        <a href="laporan.php">Laporan</a>
        <a href="<?php echo $logoutAction; ?>">Logout</a>
    </nav>

    <footer class="footer">
        <p>Perpustakaan BYS</p>
        <p>&copy; 2025 Perpustakaan BYS. All rights reserved.</p>
    </footer>

    <div id="peminjamanEditModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>Update Status Peminjaman</h2>
            <form method="post" name="formUpdatePinjam" action="<?php echo $editFormAction; ?>">
                <table align="center">
                    <tr valign="baseline">
                        <td align="right">Tanggal Pengembalian:</td>
                        <td><input type="date" name="TanggalPengembalian" id="peminjamanEditTanggal"></td>
                    </tr>
                    <tr valign="baseline">
                        <td align="right">Status:</td>
                        <td>
                            <select name="StatusPeminjaman" id="peminjamanEditStatus">
                                <option value="diajukan">Diajukan</option>
                                <option value="dipinjam">Dipinjam</option>
                                <option value="dikembalikan">Dikembalikan</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="baseline">
                        <td align="right">&nbsp;</td>
                        <td><input type="submit" class="input-button" value="Simpan"></td>
                    </tr>
                </table>
                <input type="hidden" name="PeminjamanID" id="peminjamanEditID" value="">
                <input type="hidden" name="MM_update" value="formUpdatePinjam">
            </form>
        </div>
    </div>

    <script src="../assets/script.js"></script>
</body>
</html>
<?php
mysql_free_result($Ruser);
if ($totalRows_Rpeminjam > 0) {
  mysql_data_seek($Rpeminjam, 0);
}
mysql_free_result($Rpeminjam);
if ($totalRows_Rbuku > 0) {
  mysql_data_seek($Rbuku, 0);
}
mysql_free_result($Rbuku);
if ($totalRows_Rpeminjaman > 0) {
  mysql_data_seek($Rpeminjaman, 0);
}
mysql_free_result($Rpeminjaman);
if ($totalRows_Rkoleksi > 0) {
  mysql_data_seek($Rkoleksi, 0);
}
mysql_free_result($Rkoleksi);
if ($totalRows_Rulasan > 0) {
  mysql_data_seek($Rulasan, 0);
}
mysql_free_result($Rulasan);
?>
