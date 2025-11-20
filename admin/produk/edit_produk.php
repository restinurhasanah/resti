<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
  header("Location: ../welcome.php");
  exit;
}

include '../../koneksi.php';

// --- Ambil data produk berdasarkan ID ---
$id_produk = (int)($_GET['id'] ?? 0);
$q = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk = $id_produk");
$p = mysqli_fetch_assoc($q);

if (!$p) {
  echo "Produk tidak ditemukan";
  exit;
}

$pesanError = "";

// --- Proses update produk ---
if (isset($_POST['simpan'])) {
  $nama     = mysqli_real_escape_string($koneksi, trim($_POST['nama_produk']));
  $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
  $harga    = (int)$_POST['harga'];
  $stok     = (int)$_POST['stok'];
  $foto     = $p['foto'];

  // === Validasi stok minimal ===
  if ($stok < 1) {
    $pesanError = "❌ Stok tidak boleh kurang dari 1!";
  }

  // === Validasi harga minimal ===
  elseif ($harga < 1000) {
    $pesanError = "❌ Harga tidak boleh kurang dari Rp 1.000!";
  }

  // === Cek duplikat nama + kategori (kecuali produk ini sendiri) ===
  else {
    $cekDuplikat = mysqli_query($koneksi, "
      SELECT id_produk FROM produk 
      WHERE LOWER(nama_produk) = LOWER('$nama') 
      AND LOWER(kategori) = LOWER('$kategori')
      AND id_produk != $id_produk
    ");
    if (mysqli_num_rows($cekDuplikat) > 0) {
      $pesanError = "❌ Produk dengan nama dan kategori yang sama sudah ada!";
    }
  }

  // === Jika semua valid, baru update ===
  if (empty($pesanError)) {

    // Jika user upload foto baru
    if (!empty($_FILES['foto']['name'])) {
      $folderUpload = "../uploads/";
      $ext  = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
      $namaBaru = time() . '_' . uniqid() . '.' . $ext;

      // Hapus foto lama (jika ada)
      if (!empty($p['foto']) && file_exists($folderUpload . $p['foto'])) {
        unlink($folderUpload . $p['foto']);
      }

      // Pindahkan foto baru ke folder upload
      move_uploaded_file($_FILES['foto']['tmp_name'], $folderUpload . $namaBaru);
      $foto = $namaBaru;
    }

    // Simpan ke database
    mysqli_query($koneksi, "
      UPDATE produk 
      SET nama_produk='$nama', kategori='$kategori', harga=$harga, stok=$stok, foto='$foto'
      WHERE id_produk=$id_produk
    ");

    header("Location: kelola_produk.php");
    exit;
  }
}
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Produk</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {background: linear-gradient(to bottom, #252525ff, #464646ff) !important; }
    .content { margin-left: 260px; padding: 20px; }
    .card { border: none; border-radius: 10px; }
    .preview-img {
      width: 180px;
      height: 180px;
      object-fit: cover;
      border: 1px solid #ddd;
      border-radius: 10px;
      background: #fff;
      margin-bottom: 10px;
    }
  </style>
</head>

<body>
  <?php include '../sidebar.php'; ?>

  <div class="content">
    <h4 class="mb-3"  style="text-align:center" > Edit Produk</h4>

    <div class="card p-4 shadow-sm">
      <?php if (!empty($pesanError)): ?>
        <div class="alert alert-danger"><?= $pesanError ?></div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Nama Produk</label>
          <input type="text" name="nama_produk" class="form-control" value="<?= htmlspecialchars($p['nama_produk']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Kategori</label>
          <select name="kategori" class="form-select" required>
            <option value="Dingin" <?= $p['kategori'] == 'Dingin' ? 'selected' : '' ?>>Minuman Dingin</option>
            <option value="Panas"  <?= $p['kategori'] == 'Panas' ? 'selected' : '' ?>>Minuman Panas</option>
          </select>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Harga (Rp)</label>
            <input type="number" name="harga" class="form-control" value="<?= $p['harga'] ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Stok</label>
            <input type="number" name="stok" class="form-control" value="<?= $p['stok'] ?>" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Foto Produk</label><br>
          <?php
            $pathFoto = "../uploads/" . $p['foto'];
            if (!empty($p['foto']) && file_exists($pathFoto)) {
              echo "<img src='$pathFoto' class='preview-img' alt='Foto Produk'>";
            } else {
              echo "<img src='../uploads/default.png' class='preview-img' alt='Default'>";
            }
          ?>
          <input type="file" name="foto" class="form-control mt-2">
        </div>

        <div class="text-end mt-4">
          <button type="submit" name="simpan" class="btn btn-warning"> Simpan Perubahan</button>
          <a href="kelola_produk.php" class="btn btn-dark">Batal</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
