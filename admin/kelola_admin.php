<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
  header("Location: ../index.php");
  exit;
}

include '../koneksi.php';
include 'sidebar.php';

// ========== Tambah Admin ==========
if (isset($_POST['tambah'])) {
  $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
  $username = mysqli_real_escape_string($koneksi, $_POST['username']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $level = mysqli_real_escape_string($koneksi, $_POST['level']);

  $cek = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username'");
  if (mysqli_num_rows($cek) > 0) {
    $pesan = "<div class='alert alert-danger'>Username sudah digunakan!</div>";
  } else {
    mysqli_query($koneksi, "INSERT INTO user (nama, username, password, level) VALUES ('$nama','$username','$password','$level')");
    $pesan = "<div class='alert alert-success'>Data admin/kasir berhasil ditambahkan!</div>";
  }
}

// ========== Hapus Admin ==========
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  // Jangan hapus diri sendiri
  if ($id != $_SESSION['id_user']) {
    mysqli_query($koneksi, "DELETE FROM user WHERE id_user='$id'");
    $pesan = "<div class='alert alert-warning'>Data admin/kasir berhasil dihapus!</div>";
  } else {
    $pesan = "<div class='alert alert-danger'>Kamu tidak bisa menghapus akun kamu sendiri!</div>";
  }
}

// ========== Pencarian ==========
$cari = $_GET['cari'] ?? '';
$where = $cari ? "WHERE nama LIKE '%$cari%' OR username LIKE '%$cari%'" : '';
$data = mysqli_query($koneksi, "SELECT * FROM user $where ORDER BY level, nama");
?>

<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kelola Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div style="margin-left:260px; padding:20px;">
  <h4 class="mb-3"> Kelola Admin </h4>

  <?php if (!empty($pesan)) echo $pesan; ?>

  <!-- Form Tambah Admin -->
  <div class="card mb-4 p-3 shadow-sm">
    <h6>Tambah Admin Baru</h6>
    <form method="POST" class="row g-3 mt-1">
      <div class="col-md-3">
        <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required>
      </div>
      <div class="col-md-3">
        <input type="text" name="username" class="form-control" placeholder="Username" required>
      </div>
      <div class="col-md-3">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
      </div>
      <div class="col-md-2">
        <select name="level" class="form-select" required>
          <option value="">Level</option>
          <option value="admin">Admin</option>
          <option value="admin p">Admin P</option>
        </select>
      </div>
      <div class="col-md-1 d-grid">
        <button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
      </div>
    </form>
  </div>

  <!-- Pencarian -->
  <form class="mb-3" method="GET">
    <div class="input-group" style="max-width:300px;">
      <input type="text" name="cari" class="form-control" placeholder="Cari nama / username..." value="<?= htmlspecialchars($cari) ?>">
      <button class="btn btn-secondary">Cari</button>
    </div>
  </form>

  <!-- Tabel Data -->
  <div class="card p-3 shadow-sm">
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th width="5%">No</th>
            <th>Nama</th>
            <th>Username</th>
            <th>Level</th>
            <th width="15%">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        if (mysqli_num_rows($data) > 0) {
          while ($r = mysqli_fetch_assoc($data)) {
            echo "
            <tr>
              <td class='text-center'>$no</td>
              <td>{$r['nama']}</td>
              <td>{$r['username']}</td>
              <td class='text-center'>{$r['level']}</td>
              <td class='text-center'>
                <a href='?hapus={$r['id_user']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin hapus data ini?\")'>Hapus</a>
              </td>
            </tr>";
            $no++;
          }
        } else {
          echo "<tr><td colspan='5' class='text-center text-muted'>Tidak ada data ditemukan</td></tr>";
        }
        ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
