<?php require_once('Connections/koneksi.php'); ?>
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

  $insertGoTo = "login.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan BYS</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gaegu:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

	<footer class="footer">
		<p>Perpustakaan BYS</p>
		<p>&copy; 2025 Perpustakaan BYS. All rights reserved.</p>
	</footer>

    <div class="cursor-dot"></div>
    <div class="cursor-outline"></div>

    <header class="hero">
        <section id="daftar" class="content-section">
            <h2>Daftar</h2>
          <form method="post" name="form1" action="<?php echo $editFormAction; ?>">
            <table align="center">
              <tr valign="baseline">
                <td nowrap align="left">Username</td>
                <td nowrap align="center">:</td>
                <td><input type="text" name="Username" value="" size="32"></td>
              </tr>
              <tr valign="baseline">
                <td nowrap align="left">Password</td>
                <td nowrap align="center">:</td>
                <td><input type="password" name="Password" value="" size="32"></td>
              </tr>
              <tr valign="baseline">
                <td nowrap align="left">Email</td>
                <td nowrap align="center">:</td>
                <td><input type="text" name="Email" value="" size="32"></td>
              </tr>
              <tr valign="baseline">
                <td nowrap align="left">Nama Lengkap</td>
                <td nowrap align="center">:</td>
                <td><input type="text" name="NamaLengkap" value="" size="32"></td>
              </tr>
              <tr valign="baseline">
                <td nowrap align="left">Alamat</td>
                <td nowrap align="center">:</td>
                <td><input type="text" name="Alamat" value="" size="32"></td>
              </tr>
              <tr valign="baseline">
                <td colspan="3" align="center" nowrap><input type="submit" value="Daftar" class="input-button"></td>
              </tr>
            </table>
            <input type="hidden" name="UserID" value="">
            <input type="hidden" name="Level" value="peminjam">
            <input type="hidden" name="MM_insert" value="form1">
          </form>
          <p>&nbsp;</p>
        </section>
    </header>

    <nav class="bottom-nav">
        <a href="index.php">Beranda</a>
        <a href="login.php">Login</a>
        <a href="#" class="active">Daftar</a>
    </nav>

    <script src="assets/script.js"></script>
</body>
</html>