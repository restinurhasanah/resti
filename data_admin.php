<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'adminresmi') {
    header("Location: ../login_admin.php");
    exit();
}

include 'koneksi.php';

// ========== Tambah Admin ==========
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $level = mysqli_real_escape_string($koneksi, $_POST['level']);

    $cek = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username'");
    if ($cek && mysqli_num_rows($cek) > 0) {
        $pesan = "<div class='alert alert-danger text-center'>❌ Username sudah digunakan!</div>";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO user (nama, username, password, level, status) 
                  VALUES ('$nama', '$username', '$password_hash', '$level', 'aktif')";
        if (mysqli_query($koneksi, $query)) {
            $pesan = "<div class='alert alert-success text-center'>✅ Admin baru berhasil ditambahkan!</div>";
        } else {
            $pesan = "<div class='alert alert-danger text-center'>❌ Gagal menambahkan admin! (" . mysqli_error($koneksi) . ")</div>";
        }
    }
}

// Nonaktifkan Admin
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id > 0) {
        mysqli_query($koneksi, "UPDATE user SET status='nonaktif' WHERE id_user='$id'");
        $pesan = "<div class='alert alert-warning text-center'>Akun berhasil dinonaktifkan!</div>";
    }
}

// Update Admin
if (isset($_POST['update'])) {
    $id = (int)$_POST['id_user'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $level = mysqli_real_escape_string($koneksi, $_POST['level']);
    $password = $_POST['password'];

    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query($koneksi, "UPDATE user SET nama='$nama', username='$username', level='$level', password='$password_hash' WHERE id_user='$id'");
    } else {
        mysqli_query($koneksi, "UPDATE user SET nama='$nama', username='$username', level='$level' WHERE id_user='$id'");
    }

    $pesan = "<div class='alert alert-success text-center'>Data admin berhasil diupdate!</div>";
}

// ========== Ambil Data Admin (termasuk 'pengganti') ==========
$cari = $_GET['cari'] ?? '';
$where = $cari ? "AND (nama LIKE '%" . mysqli_real_escape_string($koneksi,$cari) . "%' OR username LIKE '%" . mysqli_real_escape_string($koneksi,$cari) . "%')" : '';
$sql = "SELECT * FROM user 
    WHERE level IN ('admin','pengganti') 
    AND status='aktif' 
    $where 
    ORDER BY nama";
$data = mysqli_query($koneksi, $sql);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kelola Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg, #dfe9f3 0%, #ffffff 100%);
    font-size: 17px;
}
.header-box {
    background: rgba(255,255,255,0.7);
    backdrop-filter: blur(8px);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}
.btn-glass {
    background: rgba(0,0,0,0.4);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 8px 18px;
}
.btn-glass:hover {
    background: rgba(0,0,0,0.6);
}
.card { border-radius: 15px; }
.table td, .table th { vertical-align: middle; }
</style>
</head>

<body>
<div class="container mt-4">

    <!-- HEADER -->
    <div class="header-box text-center">
        <h2 class="fw-bold">Kelola Admin</h2>

        <!-- Tombol kembali -->
        <div class="mt-3 d-flex justify-content-center gap-3">
            <a href="login_admin.php" class="btn btn-sm btn-glass">🔙 Kembali ke Login</a>
          
        </div>
    </div>

    <?= $pesan ?? '' ?>

    <!-- Form Tambah Admin -->
    <div class="card shadow-sm p-4 mb-4">
        <h5 class="fw-bold">Tambah Admin Baru</h5>
        <form method="POST" class="row g-3 mt-2">
            <div class="col-md-3"><input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required></div>
            <div class="col-md-3"><input type="text" name="username" class="form-control" placeholder="Username" required></div>
            <div class="col-md-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
            <div class="col-md-2">
                <select name="level" class="form-select" required>
                    <option value="">Level</option>
                    <option value="admin">Admin</option>
                    <option value="pengganti">Pengganti</option>
                </select>
            </div>
            <div class="col-md-1 d-grid"><button type="submit" name="tambah" class="btn btn-dark">Tambah</button></div>
        </form>
    </div>

    <!-- Pencarian -->
    <div class="input-group mb-3" style="max-width:700px; margin:auto;">
        <input type="text" id="cari" name="cari" class="form-control" placeholder="Cari nama / username..." value="<?= htmlspecialchars($cari) ?>">
        <button class="btn btn-outline-secondary" type="button" id="clearSearch">&times;</button>
    </div>

    <!-- Tabel Admin -->
    <div class="card shadow-sm p-3">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Level</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                if ($data && mysqli_num_rows($data) > 0) {
                    while ($r = mysqli_fetch_assoc($data)) {
                        $pass = str_repeat("•", 8);
                ?>
                <tr>
                    <td><?= $no ?></td>
                    <td><?= htmlspecialchars($r['nama']) ?></td>
                    <td><?= htmlspecialchars($r['username']) ?></td>
                    <td><?= $pass ?></td>
                    <td><?= htmlspecialchars($r['level']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#modal<?= $r['id_user'] ?>">Edit</button>
                        <a href="?hapus=<?= $r['id_user'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menonaktifkan akun ini?')">Hapus</a>
                    </td>
                </tr>

                <!-- Modal Edit -->
                <div class="modal fade" id="modal<?= $r['id_user'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST">
                        <div class="modal-header">
                          <h5 class="modal-title">Update data - <?= htmlspecialchars($r['nama']) ?></h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="id_user" value="<?= $r['id_user'] ?>">
                          <div class="mb-3"><label>Nama</label><input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($r['nama']) ?>" required></div>
                          <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" value="<?= htmlspecialchars($r['username']) ?>" required></div>
                          <div class="mb-3"><label>Password Baru</label><input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah"></div>
                          <div class="mb-3">
                            <label>Level</label>
                            <select name="level" class="form-select" required>
                                <option value="admin" <?= $r['level']=='admin'?'selected':'' ?>>Admin</option>
                                <option value="pengganti" <?= $r['level']=='pengganti'?'selected':'' ?>>Pengganti</option>
                            </select>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="submit" name="update" class="btn btn-dark">Simpan</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                <?php
                    $no++;
                    } // end while
                } else {
                    echo '<tr><td colspan="6" class="text-muted">Tidak ada data admin aktif</td></tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('cari').addEventListener('keyup', function(){
    clearTimeout(window.searchDelay);
    window.searchDelay = setTimeout(() => {
        const val = this.value.trim();
        window.location.href = val ? "?cari="+encodeURIComponent(val) : "data_admin.php";
    }, 400);
});
document.getElementById('clearSearch').onclick = () => {
    window.location.href = "data_admin.php";
};
</script>

</body>
</html>
