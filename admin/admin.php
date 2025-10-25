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
$query_Rstats = "SELECT 
  (SELECT COUNT(*) FROM buku) AS TotalBuku,
  (SELECT COUNT(*) FROM kategoribuku) AS TotalKategori,
  (SELECT COUNT(*) FROM `user`) AS TotalUser,
  (SELECT COUNT(*) FROM peminjaman) AS TotalPeminjaman,
  (SELECT COUNT(*) FROM `user` WHERE Level = 'administrator') AS TotalAdmin,
  (SELECT COUNT(*) FROM `user` WHERE Level = 'petugas') AS TotalPetugas,
  (SELECT COUNT(*) FROM `user` WHERE Level = 'peminjam') AS TotalPeminjam,
  (SELECT COUNT(*) FROM peminjaman WHERE StatusPeminjaman = 'diajukan') AS TotalDiajukan,
  (SELECT COUNT(*) FROM peminjaman WHERE StatusPeminjaman = 'dipinjam') AS TotalDipinjam,
  (SELECT COUNT(*) FROM peminjaman WHERE StatusPeminjaman = 'dikembalikan') AS TotalDikembalikan,
  (SELECT COUNT(*) FROM koleksipribadi) AS TotalKoleksi,
  (SELECT COUNT(*) FROM ulasanbuku) AS TotalUlasan";
$Rstats = mysql_query($query_Rstats, $koneksi) or die(mysql_error());
$row_Rstats = mysql_fetch_assoc($Rstats);

mysql_select_db($database_koneksi, $koneksi);
$query_RrecentBooks = "SELECT buku.BukuID, buku.Judul, kategoribuku.NamaKategori FROM buku LEFT JOIN kategoribuku ON buku.KategoriID = kategoribuku.KategoriID ORDER BY buku.BukuID DESC LIMIT 5";
$RrecentBooks = mysql_query($query_RrecentBooks, $koneksi) or die(mysql_error());
$row_RrecentBooks = mysql_fetch_assoc($RrecentBooks);
$totalRows_RrecentBooks = mysql_num_rows($RrecentBooks);

mysql_select_db($database_koneksi, $koneksi);
$query_RrecentLoans = "SELECT peminjaman.PeminjamanID, `user`.Username, buku.Judul, peminjaman.TanggalPeminjaman, peminjaman.StatusPeminjaman FROM peminjaman INNER JOIN `user` ON peminjaman.UserID = `user`.UserID INNER JOIN buku ON peminjaman.BukuID = buku.BukuID ORDER BY peminjaman.PeminjamanID DESC LIMIT 5";
$RrecentLoans = mysql_query($query_RrecentLoans, $koneksi) or die(mysql_error());
$row_RrecentLoans = mysql_fetch_assoc($RrecentLoans);
$totalRows_RrecentLoans = mysql_num_rows($RrecentLoans);

mysql_select_db($database_koneksi, $koneksi);
$query_RrecentUsers = "SELECT UserID, Username, Level FROM `user` ORDER BY UserID DESC LIMIT 5";
$RrecentUsers = mysql_query($query_RrecentUsers, $koneksi) or die(mysql_error());
$row_RrecentUsers = mysql_fetch_assoc($RrecentUsers);
$totalRows_RrecentUsers = mysql_num_rows($RrecentUsers);
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
            <h2>Selamat datang, <?php echo $row_Ruser['NamaLengkap'] ? $row_Ruser['NamaLengkap'] : $row_Ruser['Username']; ?></h2>
            <p>Kelola seluruh aktivitas perpustakaan dari satu tempat dengan ringkasan data terbaru.</p>
        </section>

        <section class="content-section card-container">
            <div class="card">
                <h3>Total Buku</h3>
                <p><?php echo $row_Rstats['TotalBuku']; ?> judul</p>
            </div>
            <div class="card">
                <h3>Kategori</h3>
                <p><?php echo $row_Rstats['TotalKategori']; ?> kategori</p>
            </div>
            <div class="card">
                <h3>Pengguna</h3>
                <p><?php echo $row_Rstats['TotalUser']; ?> akun</p>
                <span class="pill">Admin <?php echo $row_Rstats['TotalAdmin']; ?> • Petugas <?php echo $row_Rstats['TotalPetugas']; ?> • Peminjam <?php echo $row_Rstats['TotalPeminjam']; ?></span>
            </div>
            <div class="card">
                <h3>Peminjaman</h3>
                <p><?php echo $row_Rstats['TotalPeminjaman']; ?> transaksi</p>
                <span class="pill diajukan">Diajukan <?php echo $row_Rstats['TotalDiajukan']; ?></span>
                <span class="pill dipinjam">Dipinjam <?php echo $row_Rstats['TotalDipinjam']; ?></span>
                <span class="pill dikembalikan">Dikembalikan <?php echo $row_Rstats['TotalDikembalikan']; ?></span>
            </div>
            <div class="card">
                <h3>Koleksi & Ulasan</h3>
                <p><?php echo $row_Rstats['TotalKoleksi']; ?> koleksi pribadi</p>
                <span class="pill"><?php echo $row_Rstats['TotalUlasan']; ?> ulasan</span>
            </div>
        </section>

        <section class="content-section">
            <h2>Update Terbaru</h2>
            <div class="two-column-layout">
                <div class="column">
                    <h3>Buku Terbaru</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Judul</th>
                                <th>Kategori</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($totalRows_RrecentBooks > 0) { ?>
                            <?php do { ?>
                                <tr>
                                    <td><?php echo $row_RrecentBooks['BukuID']; ?></td>
                                    <td><?php echo $row_RrecentBooks['Judul']; ?></td>
                                    <td><?php echo $row_RrecentBooks['NamaKategori'] ? $row_RrecentBooks['NamaKategori'] : 'Tanpa Kategori'; ?></td>
                                </tr>
                            <?php } while ($row_RrecentBooks = mysql_fetch_assoc($RrecentBooks)); ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="3">Belum ada data buku.</td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="column">
                    <h3>Peminjaman Terakhir</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Peminjam</th>
                                <th>Buku</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($totalRows_RrecentLoans > 0) { ?>
                            <?php do { ?>
                                <tr>
                                    <td><?php echo $row_RrecentLoans['PeminjamanID']; ?></td>
                                    <td><?php echo $row_RrecentLoans['Username']; ?></td>
                                    <td><?php echo $row_RrecentLoans['Judul']; ?></td>
                                    <td>
                                        <span class="pill <?php echo strtolower($row_RrecentLoans['StatusPeminjaman']); ?>">
                                            <?php echo $row_RrecentLoans['StatusPeminjaman']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php } while ($row_RrecentLoans = mysql_fetch_assoc($RrecentLoans)); ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="4">Belum ada transaksi peminjaman.</td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="content-section">
            <h3>Pengguna Terbaru</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Level</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($totalRows_RrecentUsers > 0) { ?>
                    <?php do { ?>
                        <tr>
                            <td><?php echo $row_RrecentUsers['UserID']; ?></td>
                            <td><?php echo $row_RrecentUsers['Username']; ?></td>
                            <td><?php echo ucfirst($row_RrecentUsers['Level']); ?></td>
                        </tr>
                    <?php } while ($row_RrecentUsers = mysql_fetch_assoc($RrecentUsers)); ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="3">Belum ada data pengguna.</td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </section>
    </main>

    <nav class="bottom-nav">
        <a href="admin.php" class="active">Dashboard</a>
        <a href="data_user.php">User</a>
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

    <script src="../assets/script.js"></script>
</body>
</html>
<?php
mysql_free_result($Ruser);
mysql_free_result($Rstats);
if ($totalRows_RrecentBooks > 0) {
  mysql_data_seek($RrecentBooks, 0);
}
mysql_free_result($RrecentBooks);
if ($totalRows_RrecentLoans > 0) {
  mysql_data_seek($RrecentLoans, 0);
}
mysql_free_result($RrecentLoans);
if ($totalRows_RrecentUsers > 0) {
  mysql_data_seek($RrecentUsers, 0);
}
mysql_free_result($RrecentUsers);
?>
