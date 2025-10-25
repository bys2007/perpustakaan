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
$query_Rstat = "SELECT 
  (SELECT COUNT(*) FROM buku) AS TotalBuku,
  (SELECT COUNT(*) FROM kategoribuku) AS TotalKategori,
  (SELECT COUNT(*) FROM `user`) AS TotalUser,
  (SELECT COUNT(*) FROM peminjaman) AS TotalPeminjaman,
  (SELECT COUNT(*) FROM peminjaman WHERE StatusPeminjaman = 'diajukan') AS TotalDiajukan,
  (SELECT COUNT(*) FROM peminjaman WHERE StatusPeminjaman = 'dipinjam') AS TotalDipinjam,
  (SELECT COUNT(*) FROM peminjaman WHERE StatusPeminjaman = 'dikembalikan') AS TotalDikembalikan,
  (SELECT COUNT(*) FROM koleksipribadi) AS TotalKoleksi,
  (SELECT COUNT(*) FROM ulasanbuku) AS TotalUlasan";
$Rstat = mysql_query($query_Rstat, $koneksi) or die(mysql_error());
$row_Rstat = mysql_fetch_assoc($Rstat);

$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : "";
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : "";

$filters = array();
if ($startDate != "") {
  $filters[] = "peminjaman.TanggalPeminjaman >= " . GetSQLValueString($startDate, "date");
}
if ($endDate != "") {
  $filters[] = "peminjaman.TanggalPeminjaman <= " . GetSQLValueString($endDate, "date");
}
$filterClause = "";
if (!empty($filters)) {
  $filterClause = " WHERE " . implode(" AND ", $filters);
}

mysql_select_db($database_koneksi, $koneksi);
$query_Rpeminjaman = "SELECT peminjaman.PeminjamanID, peminjaman.TanggalPeminjaman, peminjaman.TanggalPengembalian, peminjaman.StatusPeminjaman, `user`.Username, buku.Judul 
FROM peminjaman 
INNER JOIN `user` ON peminjaman.UserID = `user`.UserID 
INNER JOIN buku ON peminjaman.BukuID = buku.BukuID" . $filterClause . " 
ORDER BY peminjaman.TanggalPeminjaman DESC";
$Rpeminjaman = mysql_query($query_Rpeminjaman, $koneksi) or die(mysql_error());
$row_Rpeminjaman = mysql_fetch_assoc($Rpeminjaman);
$totalRows_Rpeminjaman = mysql_num_rows($Rpeminjaman);

mysql_select_db($database_koneksi, $koneksi);
$query_Rstatus = "SELECT peminjaman.StatusPeminjaman, COUNT(*) AS Total FROM peminjaman" . $filterClause . " GROUP BY peminjaman.StatusPeminjaman";
$Rstatus = mysql_query($query_Rstatus, $koneksi) or die(mysql_error());
$row_Rstatus = mysql_fetch_assoc($Rstatus);
$totalRows_Rstatus = mysql_num_rows($Rstatus);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gaegu:wght@400;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            body {
                cursor: auto;
            }
            .bottom-nav, .footer, .print-actions, .cursor-dot, .cursor-outline {
                display: none !important;
            }
            main {
                padding: 0;
            }
            .content-section {
                box-shadow: none;
            }
        }
        .print-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .print-actions button {
            border: none;
            border-radius: 999px;
            padding: 0.75rem 1.5rem;
            font-family: var(--handwritten-font);
            font-size: 1rem;
            background-color: var(--highlight-color);
            color: #fff;
            cursor: pointer;
        }
        .print-actions button.secondary {
            background-color: var(--accent-color);
        }
        .print-actions button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

    <div class="cursor-dot"></div>
    <div class="cursor-outline"></div>

    <main>
        <section class="content-section">
            <h2>Laporan Perpustakaan</h2>
            <p>Halo <?php echo $row_Ruser['NamaLengkap'] ? $row_Ruser['NamaLengkap'] : $row_Ruser['Username']; ?>, gunakan halaman ini untuk mencetak ringkasan aktivitas perpustakaan.</p>
        </section>

        <section class="content-section card-container">
            <div class="card">
                <h3>Total Buku</h3>
                <p><?php echo $row_Rstat['TotalBuku']; ?> judul</p>
            </div>
            <div class="card">
                <h3>Kategori</h3>
                <p><?php echo $row_Rstat['TotalKategori']; ?> kategori</p>
            </div>
            <div class="card">
                <h3>Pengguna</h3>
                <p><?php echo $row_Rstat['TotalUser']; ?> akun</p>
            </div>
            <div class="card">
                <h3>Transaksi Peminjaman</h3>
                <p><?php echo $row_Rstat['TotalPeminjaman']; ?> transaksi</p>
                <span class="pill diajukan">Diajukan <?php echo $row_Rstat['TotalDiajukan']; ?></span>
                <span class="pill dipinjam">Dipinjam <?php echo $row_Rstat['TotalDipinjam']; ?></span>
                <span class="pill dikembalikan">Dikembalikan <?php echo $row_Rstat['TotalDikembalikan']; ?></span>
            </div>
            <div class="card">
                <h3>Koleksi Pribadi</h3>
                <p><?php echo $row_Rstat['TotalKoleksi']; ?> catatan</p>
            </div>
            <div class="card">
                <h3>Ulasan Buku</h3>
                <p><?php echo $row_Rstat['TotalUlasan']; ?> ulasan</p>
            </div>
        </section>

        <section class="content-section">
            <div class="print-actions">
                <form method="get" action="laporan.php">
                    <input type="hidden" name="start_date" value="<?php echo htmlentities($startDate, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="end_date" value="<?php echo htmlentities($endDate, ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="button" onclick="window.print()">Cetak Laporan</button>
                </form>
            </div>
            <h3>Filter Peminjaman</h3>
            <form method="get" action="laporan.php" class="form-buku">
              <table>
                <tr>
                  <td align="left">Tanggal Mulai</td>
                  <td><input type="date" name="start_date" value="<?php echo htmlentities($startDate, ENT_QUOTES, 'UTF-8'); ?>"></td>
                  <td align="left">Tanggal Akhir</td>
                  <td><input type="date" name="end_date" value="<?php echo htmlentities($endDate, ENT_QUOTES, 'UTF-8'); ?>"></td>
                  <td align="center">
                    <button type="submit" class="input-button">Terapkan</button>
                  </td>
                </tr>
              </table>
            </form>
        </section>

        <section class="content-section">
            <h3>Ringkasan Status Peminjaman</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($totalRows_Rstatus > 0) { ?>
                        <?php do { ?>
                            <tr>
                                <td><?php echo ucfirst($row_Rstatus['StatusPeminjaman']); ?></td>
                                <td><?php echo $row_Rstatus['Total']; ?></td>
                            </tr>
                        <?php } while ($row_Rstatus = mysql_fetch_assoc($Rstatus)); ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="2">Belum ada transaksi untuk periode ini.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>

        <section class="content-section">
            <h3>Detail Transaksi Peminjaman</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Peminjam</th>
                        <th>Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Status</th>
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
                            </tr>
                        <?php } while ($row_Rpeminjaman = mysql_fetch_assoc($Rpeminjaman)); ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6">Tidak ada data peminjaman untuk filter yang dipilih.</td>
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
        <a href="data_peminjaman.php">Peminjaman</a>
        <a href="laporan.php" class="active">Laporan</a>
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
mysql_free_result($Rstat);
if ($totalRows_Rpeminjaman > 0) {
  mysql_data_seek($Rpeminjaman, 0);
}
mysql_free_result($Rpeminjaman);
if ($totalRows_Rstatus > 0) {
  mysql_data_seek($Rstatus, 0);
}
mysql_free_result($Rstatus);
?>
