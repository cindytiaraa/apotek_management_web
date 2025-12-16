<?php 
include "../../koneksi.php";

$id = $_GET['id'];
$q = mysqli_query($koneksi, "SELECT * FROM user WHERE iduser = $id");
$data = mysqli_fetch_assoc($q);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit User</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "includes/header.php"; ?>
<div class="container">
  <?php include "includes/sidebar.php"; ?>
  <main class="content">
    <h3>✏ Edit User</h3>
    <form method="POST" action="user.php" style="max-width:400px; margin-top:20px;">
      <input type="hidden" name="iduser" value="<?php echo $data['iduser']; ?>">
      <label>Username:</label><br>
      <input type="text" name="username" value="<?php echo $data['username']; ?>" required style="width:100%; padding:8px; margin-bottom:10px;"><br>
      <label>Password (kosongkan jika tidak diganti):</label><br>
      <input type="password" name="password" placeholder="Password baru" style="width:100%; padding:8px; margin-bottom:10px;"><br>
      <label>Role:</label><br>
      <select name="idrole" required style="width:100%; padding:8px; margin-bottom:10px;">
        <?php
          $roles = mysqli_query($koneksi, "SELECT * FROM role");
          while ($r = mysqli_fetch_assoc($roles)) {
            $sel = $r['idrole'] == $data['idrole'] ? 'selected' : '';
            echo "<option value='{$r['idrole']}' $sel>{$r['nama_role']}</option>";
          }
        ?>
      </select>
      <button type="submit" name="update" class="btn btn-aktif" style="width:100%;">Simpan Perubahan</button>
    </form>
  </main>
</div>
<?php include "includes/footer.php"; ?>
</body>
</html>