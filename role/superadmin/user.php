<?php 
include "../../koneksi.php";

// --- Proses ubah status aktif/nonaktif ---
if (isset($_GET['toggle'])) {
  $iduser = $_GET['toggle'];
  $status = $_GET['status'];

  mysqli_query($koneksi, "UPDATE user SET status = $status WHERE iduser = $iduser");
  header("Location: user.php");
  exit();
}

// --- Proses tambah user baru ---
if (isset($_POST['tambah'])) {
  $username = mysqli_real_escape_string($koneksi, $_POST['username']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $idrole = $_POST['idrole'];

  $cek = mysqli_query($koneksi, "SELECT * FROM user WHERE username = '$username'");
  if (mysqli_num_rows($cek) == 0) {
    mysqli_query($koneksi, "INSERT INTO user (username, password, idrole, status) VALUES ('$username', '$password', '$idrole', 1)");
    header("Location: user.php?msg=added");
    exit();
  } else {
    $error = "⚠ Username sudah digunakan!";
  }
}

// --- Proses update user ---
if (isset($_POST['update'])) {
  $iduser = $_POST['iduser'];
  $username = mysqli_real_escape_string($koneksi, $_POST['username']);
  $idrole = $_POST['idrole'];

  if (!empty($_POST['password'])) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    mysqli_query($koneksi, "UPDATE user SET username='$username', password='$password', idrole='$idrole' WHERE iduser='$iduser'");
  } else {
    mysqli_query($koneksi, "UPDATE user SET username='$username', idrole='$idrole' WHERE iduser='$iduser'");
  }
  
  header("Location: user.php?msg=updated");
  exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manajemen User - Superadmin</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "includes/header.php"; ?>

<div class="container">
  <?php include "includes/sidebar.php"; ?>

  <main class="content">
    <div class="page-title-box">
      <h2>👤 Data User</h2>
      <p>Kelola akun pengguna dan hak akses sistem</p>
    </div>

    <?php if(isset($error)): ?>
      <p class="error-msg"><?php echo $error; ?></p>
    <?php endif; ?>

    <!-- 🔹 Form Tambah User -->
    <div class="box" style="max-width: 100%; margin-bottom: 25px;">
      <h4 style="margin-bottom:12px;">Tambah User Baru</h4>
      <form method="POST" class="form-tambah" style="display:flex; gap:10px; flex-wrap:wrap;">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="idrole" required>
          <option value="">Pilih Role</option>
          <?php
            $roles = mysqli_query($koneksi, "SELECT * FROM role");
            while ($r = mysqli_fetch_assoc($roles)) {
              echo "<option value='{$r['idrole']}'>{$r['nama_role']}</option>";
            }
          ?>
        </select>
        <button type="submit" name="tambah" class="btn btn-add">Tambah</button>
      </form>
    </div>

    <!-- 🔹 Search + Filter (Versi Vendor Style) -->
     <div class="box" style="max-width: 100%; margin-bottom: 25px;">
        <form method="GET" class="search-form" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
          <input 
            type="text" 
            name="keyword" 
            placeholder="Cari user..." 
            value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>" 
            style="padding:8px; border:1px solid #9fd4ff; border-radius:20px; width:220px;"
          >

          <select name="status" onchange="this.form.submit()" style="padding:8px; border-radius:20px; border:1px solid #9fd4ff;">
            <option value="">Semua</option>
            <option value="1" <?php if(isset($_GET['status']) && $_GET['status']=='1') echo 'selected'; ?>>Aktif</option>
            <option value="0" <?php if(isset($_GET['status']) && $_GET['status']=='0') echo 'selected'; ?>>Nonaktif</option>
          </select>
        </form>

        <!-- 🔹Tabel Data User -->
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Role</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $keyword = $_GET['keyword'] ?? '';
              $statusFilter = $_GET['status'] ?? '';

              $query = "
                SELECT 
                  u.iduser, u.username, r.nama_role, 
                  CASE WHEN u.status = 1 THEN 'Aktif' ELSE 'Nonaktif' END AS status
                FROM user u
                JOIN role r ON u.idrole = r.idrole
                WHERE 1
              ";

              if (!empty($keyword)) {
                $query .= " AND (u.username LIKE '%$keyword%' OR r.nama_role LIKE '%$keyword%')";
              }

              if ($statusFilter !== '') {
                $query .= " AND u.status = $statusFilter";
              }

              $query .= " ORDER BY u.iduser ASC";
              $result = mysqli_query($koneksi, $query);

              if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                  $status = $row['status'];
                  $btnLabel = $status == 'Aktif' ? 'Nonaktifkan' : 'Aktifkan';
                  $btnColor = $status == 'Aktif' ? 'btn-nonaktif' : 'btn-aktif';
                  $newStatus = $status == 'Aktif' ? 0 : 1;

                  echo "
                  <tr>
                    <td>{$row['iduser']}</td>
                    <td>{$row['username']}</td>
                    <td>{$row['nama_role']}</td>
                    <td><span class='status ".($status=='Aktif'?'aktif':'nonaktif')."'>{$status}</span></td>
                    <td>
                      <a href='user_edit.php?id={$row['iduser']}' class='btn btn-edit'>Edit</a>
                      <a href='user.php?toggle={$row['iduser']}&status=$newStatus' class='btn $btnColor'>$btnLabel</a>
                    </td>
                  </tr>";
                }
              } else {
                echo "<tr><td colspan='5' style='text-align:center;'>Tidak ada data user</td></tr>";
              }
            ?>
          </tbody>
        </table>

    </div>
  </main>
</div>

<?php include "includes/footer.php"; ?>
</body>
</html>