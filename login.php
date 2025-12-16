<?php
session_start();
include "koneksi.php";

// Jika sudah login, langsung arahkan ke dashboard sesuai role
if (isset($_SESSION['idrole'])) {
  if ($_SESSION['idrole'] == 1) {
    header("Location: role/superadmin/dashboard.php");
  } else {
    header("Location: role/admin/dashboard.php");
  }
  exit;
}

// Proses login
if (isset($_POST['login'])) {
  $username = mysqli_real_escape_string($koneksi, $_POST['username']);
  $password = mysqli_real_escape_string($koneksi, $_POST['password']);

  $query = mysqli_query($koneksi, "
    SELECT iduser, username, password, idrole, status 
    FROM user 
    WHERE username='$username' AND status=1
  ");

  if (mysqli_num_rows($query) > 0) {
    $user = mysqli_fetch_assoc($query);

    if ($password === $user['password']) { // jika belum pakai hash
      $_SESSION['iduser'] = $user['iduser'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['idrole'] = $user['idrole'];

      // Redirect sesuai role
      if ($user['idrole'] == 1) {
        header("Location: role/superadmin/dashboard.php");
      } else {
        header("Location: role/admin/dashboard.php");
      }
      exit;
    } else {
      $error = "❌ Password salah!";
    }
  } else {
    $error = "⚠ Username tidak ditemukan atau akun nonaktif.";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - Apotek Kasih Ibu</title>
  <link rel="stylesheet" href="role/superadmin/style.css">
  <style>
    body {
      background: linear-gradient(135deg, #36a2ff, #1c75d8);
      font-family: 'Poppins', sans-serif;
      display: flex; justify-content: center; align-items: center;
      height: 100vh; color: #333;
    }
    .login-box {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      padding: 30px 40px;
      width: 350px;
      text-align: center;
    }
    h2 { color: #007bff; margin-bottom: 20px; }
    input {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      border-radius: 8px;
      border: 1px solid #b8e2f2;
    }
    button {
      width: 100%;
      background: #007bff;
      color: #fff;
      border: none;
      padding: 10px;
      border-radius: 8px;
      cursor: pointer;
      margin-top: 10px;
      font-weight: bold;
    }
    button:hover { background: #0056b3; }
    .error { color: red; font-size: 14px; margin-top: 5px; }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>💊 Login Apotek Kasih Ibu</h2>
    <form method="POST">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit" name="login">Masuk</button>
      <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    </form>
  </div>
</body>
</html>