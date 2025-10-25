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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO `user` (UserID, Username, Password, Email, NamaLengkap, Alamat, `Level`) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['UserID'], "int"),
                       GetSQLValueString($_POST['Username'], "text"),
                       GetSQLValueString($_POST['Password'], "text"),
                       GetSQLValueString($_POST['Email'], "text"),
                       GetSQLValueString($_POST['NamaLengkap'], "text"),
                       GetSQLValueString($_POST['Alamat'], "text"),
                       GetSQLValueString($_POST['Level'], "text"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($insertSQL, $koneksi) or die(mysql_error());

  $insertGoTo = "data_user.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "formUpdate")) {
  $updateSQL = sprintf("UPDATE `user` SET Username=%s, Password=%s, Email=%s, NamaLengkap=%s, Alamat=%s, `Level`=%s WHERE UserID=%s",
                       GetSQLValueString($_POST['Username'], "text"),
                       GetSQLValueString($_POST['Password'], "text"),
                       GetSQLValueString($_POST['Email'], "text"),
                       GetSQLValueString($_POST['NamaLengkap'], "text"),
                       GetSQLValueString($_POST['Alamat'], "text"),
                       GetSQLValueString($_POST['Level'], "text"),
                       GetSQLValueString($_POST['UserID'], "int"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($updateSQL, $koneksi) or die(mysql_error());

  $updateGoTo = "data_user.php";
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
$query_user = "SELECT * FROM `user`";
$user = mysql_query($query_user, $koneksi) or die(mysql_error());
$row_user = mysql_fetch_assoc($user);
$totalRows_user = mysql_num_rows($user);
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
            <h2>Kelola Pengguna</h2>
            <p>Tambah akun baru untuk administrator, petugas, maupun peminjam. Gunakan tombol edit untuk memperbarui data.</p>
        </section>

        <section class="content-section form-buku">
          <form method="post" name="form1" action="<?php echo $editFormAction; ?>">
            <table>
              <tr valign="baseline">
                <td align="left">Username</td>
                <td><input type="text" name="Username" value="" size="32" required></td>
              </tr>
              <tr valign="baseline">
                <td align="left">Password</td>
                <td><input type="password" name="Password" value="" size="32" required></td>
              </tr>
              <tr valign="baseline">
                <td align="left">Email</td>
                <td><input type="text" name="Email" value="" size="32" required></td>
              </tr>
              <tr valign="baseline">
                <td align="left">Nama Lengkap</td>
                <td><input type="text" name="NamaLengkap" value="" size="32" required></td>
              </tr>
              <tr valign="baseline">
                <td align="left">Alamat</td>
                <td><input type="text" name="Alamat" value="" size="32" required></td>
              </tr>
              <tr valign="baseline">
                <td align="left">Level</td>
                <td>
                  <select name="Level" required>
                    <option value="">--Pilih level--</option>
                    <option value="administrator">Administrator</option>
                    <option value="petugas">Petugas</option>
                    <option value="peminjam">Peminjam</option>
                  </select>
                </td>
              </tr>
              <tr valign="baseline">
                <td colspan="2" align="center">
                  <button type="submit" class="input-button">Tambah Pengguna</button>
                </td>
              </tr>
            </table>
            <input type="hidden" name="UserID" value="">
            <input type="hidden" name="MM_insert" value="form1">
          </form>
        </section>

        <section class="content-section">
          <h3>Daftar Pengguna</h3>
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Nama Lengkap</th>
                <th>Level</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($totalRows_user > 0) { ?>
                <?php do { ?>
                  <tr>
                    <td><?php echo $row_user['UserID']; ?></td>
                    <td><?php echo $row_user['Username']; ?></td>
                    <td><?php echo $row_user['Email']; ?></td>
                    <td><?php echo $row_user['NamaLengkap']; ?></td>
                    <td><?php echo ucfirst($row_user['Level']); ?></td>
                    <td class="action-links">
                      <a href="#" class="edit-btn user-edit-btn"
                         data-id="<?php echo $row_user['UserID']; ?>"
                         data-username="<?php echo htmlentities($row_user['Username'], ENT_QUOTES, 'UTF-8'); ?>"
                         data-email="<?php echo htmlentities($row_user['Email'], ENT_QUOTES, 'UTF-8'); ?>"
                         data-nama="<?php echo htmlentities($row_user['NamaLengkap'], ENT_QUOTES, 'UTF-8'); ?>"
                         data-alamat="<?php echo htmlentities($row_user['Alamat'], ENT_QUOTES, 'UTF-8'); ?>"
                         data-level="<?php echo $row_user['Level']; ?>"
                         data-password="<?php echo htmlentities($row_user['Password'], ENT_QUOTES, 'UTF-8'); ?>">Edit</a>
                      <?php if ($row_Ruser['UserID'] != $row_user['UserID']) { ?>
                        <a href="delete_user.php?UserID=<?php echo $row_user['UserID']; ?>" class="delete-btn" onclick="return confirm('Yakin ingin menghapus pengguna ini?');">Delete</a>
                      <?php } ?>
                    </td>
                  </tr>
                <?php } while ($row_user = mysql_fetch_assoc($user)); ?>
              <?php } else { ?>
                <tr>
                  <td colspan="6">Belum ada data pengguna.</td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </section>
    </main>

    <nav class="bottom-nav">
        <a href="admin.php">Dashboard</a>
        <a href="data_user.php" class="active">User</a>
        <a href="data_buku.php">Buku</a>
        <a href="data_kategori.php">Kategori</a>
        <a href="data_peminjaman.php">Peminjaman</a>
        <a href="laporan.php">Laporan</a>
        <a href="<?php echo $logoutAction ?>">Logout</a>
    </nav>

    <footer class="footer">
        <p>Perpustakaan BYS</p>
        <p>&copy; 2025 Perpustakaan BYS. All rights reserved.</p>
    </footer>

    <div id="userEditModal" class="modal" style="display:none;">
      <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Edit Pengguna</h2>
        <form method="post" name="formUpdate" action="<?php echo $editFormAction; ?>">
          <table align="center">
            <tr valign="baseline">
              <td align="right">Username:</td>
              <td><input type="text" name="Username" id="userEditUsername" size="32" required></td>
            </tr>
            <tr valign="baseline">
              <td align="right">Password:</td>
              <td><input type="text" name="Password" id="userEditPassword" size="32" required></td>
            </tr>
            <tr valign="baseline">
              <td align="right">Email:</td>
              <td><input type="text" name="Email" id="userEditEmail" size="32" required></td>
            </tr>
            <tr valign="baseline">
              <td align="right">Nama Lengkap:</td>
              <td><input type="text" name="NamaLengkap" id="userEditNama" size="32" required></td>
            </tr>
            <tr valign="baseline">
              <td align="right">Alamat:</td>
              <td><input type="text" name="Alamat" id="userEditAlamat" size="32" required></td>
            </tr>
            <tr valign="baseline">
              <td align="right">Level:</td>
              <td>
                <select name="Level" id="userEditLevel" required>
                  <option value="administrator">Administrator</option>
                  <option value="petugas">Petugas</option>
                  <option value="peminjam">Peminjam</option>
                </select>
              </td>
            </tr>
            <tr valign="baseline">
              <td align="right">&nbsp;</td>
              <td><input type="submit" class="input-button" value="Update Pengguna"></td>
            </tr>
          </table>
          <input type="hidden" name="UserID" id="userEditID" value="">
          <input type="hidden" name="MM_update" value="formUpdate">
        </form>
      </div>
    </div>

    <script src="../assets/script.js"></script>
</body>
</html>
<?php
mysql_free_result($Ruser);

mysql_free_result($user);
?>
